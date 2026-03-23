# CLAUDE.md — `pokemon_api` Module

**For AI agents only.**

This file covers the architecture, responsibilities, data contracts, and implementation rules specific to the `pokemon_api` module and its companion `pokemon_api_sync`. Read this before touching any file in either module.

> - Project-wide context → `/CLAUDE.md`

---

## Module Map

```
├── pokemon_api/           ← PokeAPI client: fetches, normalises, caches raw data
│   └── CLAUDE.md          ← This file
└── pokemon_api_sync/      ← Drupal sync: maps pokemon_api data into Drupal entities
```

These are **two separate modules with a strict dependency direction**:

```
pokemon_api_sync  →  depends on  →  pokemon_api
pokemon_api       →  knows nothing about  →  Drupal entities / nodes
```

`pokemon_api` must never import or reference anything from `pokemon_api_sync`.
`pokemon_api_sync` is the only layer allowed to create or update Drupal nodes and taxonomy terms.

---

## `pokemon_api` — PokeAPI Client Module

### Purpose

Provides a clean PHP interface to the PokeAPI REST API (`https://pokeapi.co/api/v2`).
Its only job is to **fetch and return structured data**. It has no opinion about how
that data is stored in Drupal.

### Responsibilities

| Responsibility | Belongs here? |
|---|---|
| HTTP requests to PokeAPI endpoints | ✅ Yes |
| Response normalisation into typed DTOs | ✅ Yes |
| Raw response caching (Drupal Cache API) | ✅ Yes |
| Error handling and logging for HTTP failures | ✅ Yes |
| Creating `pokemon` nodes | ❌ No — `pokemon_api_sync` |
| Creating `pokemon_type` taxonomy terms | ❌ No — `pokemon_api_sync` |
| Knowing about Drupal content types or fields | ❌ No |

### Supported PokeAPI Endpoints

| Endpoint | Method | Purpose |
|---|---|---|
| `/pokemon/{id}` | `GET` | Fetch a single Pokémon by Pokédex number |
| `/pokemon?limit={n}&offset={o}` | `GET` | List Pokémon with pagination |
| `/pokemon-species/{id}` | `GET` | Fetch species data (flavor text, genera) |
| `/type/{id}` | `GET` | Fetch a single type |

Add new endpoints here when expanding coverage. Do not call endpoints not listed here without updating this table first.

### Data Transfer Objects (DTOs)

All API responses are normalised into typed value objects before leaving this module.
**Never pass raw `array` API responses to `pokemon_api_sync`** — always use a DTO.

```
src/DTO/
├── PokemonData.php         ← Pokédex number, name, height, weight, types, stats, sprite URL
├── PokemonSpeciesData.php  ← Flavor text (English), genus
└── PokemonTypeData.php     ← Type name, slot
```

**DTO rules:**
- DTOs are **readonly** value objects — use `readonly` properties (PHP 8.1+)
- DTOs carry only the fields the sync layer needs — do not mirror the full PokeAPI response
- DTOs must be constructable from a raw API response array via a named static factory: `PokemonData::fromApiResponse(array $data): self`
- DTOs have no methods other than the static factory

**✅ Good:**

```php
<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\DTO;

/**
 * Represents a single Pokémon from the PokeAPI.
 */
final readonly class PokemonData {

  /**
   * @param string[] $types
   *   Type names in slot order, e.g. ['fire', 'flying'].
   * @param array<string, int> $stats
   *   Stat name → base value, e.g. ['hp' => 45, 'attack' => 49].
   */
  public function __construct(
    public int $id,
    public string $name,
    public int $height,
    public int $weight,
    public array $types,
    public array $stats,
    public string $spriteUrl,
  ) {}

  /**
   * Creates a PokemonData from a raw /pokemon/{id} API response.
   */
  public static function fromApiResponse(array $data): self {
    return new self(
      id: (int) $data['id'],
      name: (string) $data['name'],
      height: (int) $data['height'],
      weight: (int) $data['weight'],
      types: array_map(
        fn($t) => $t['type']['name'],
        $data['types'],
      ),
      stats: array_column(
        array_map(
          fn($s) => ['name' => $s['stat']['name'], 'value' => $s['base_stat']],
          $data['stats'],
        ),
        'value',
        'name',
      ),
      spriteUrl: (string) ($data['sprites']['front_default'] ?? ''),
    );
  }

}
```

### Services

```
src/Service/
├── PokeApiClient.php       ← All HTTP calls; returns raw arrays or throws
└── PokemonRepository.php   ← Public API of this module; returns DTOs; owns caching
```

**`PokeApiClient`** is internal — only `PokemonRepository` calls it.
**`PokemonRepository`** is the only class `pokemon_api_sync` is allowed to inject.

```php
// What pokemon_api_sync sees and uses:
interface PokemonRepositoryInterface {
  public function getPokemon(int $id): PokemonData;
  public function getPokemonSpecies(int $id): PokemonSpeciesData;
  public function listPokemon(int $limit = 20, int $offset = 0): array;
}
```

Always program to the interface. `pokemon_api_sync` must inject
`PokemonRepositoryInterface`, never the concrete `PokemonRepository`.

### Caching

Raw PokeAPI responses are cached in the `pokemon_api` cache bin to avoid redundant
HTTP calls during sync runs.

- Cache bin: `pokemon_api` (declare in `pokemon_api.services.yml`)
- Cache key pattern: `pokemon_api:pokemon:{id}`, `pokemon_api:species:{id}`, `pokemon_api:type:{name}`
- TTL: 24 hours (`Cache::PERMANENT` is not appropriate — API data does change)
- Cache is checked in `PokemonRepository` before delegating to `PokeApiClient`
- On HTTP failure, do **not** cache the failure — let it retry next time

```php
// ✅ Cache check pattern in PokemonRepository
public function getPokemon(int $id): PokemonData {
  $cid = "pokemon_api:pokemon:{$id}";
  $cached = $this->cache->get($cid);
  if ($cached) {
    return $cached->data;
  }
  $raw = $this->client->fetchPokemon($id);
  $dto = PokemonData::fromApiResponse($raw);
  $this->cache->set($cid, $dto, time() + 86400);
  return $dto;
}
```

### Error Handling

- All HTTP exceptions from Guzzle are caught in `PokeApiClient`
- On failure, log with `@channel pokemon_api` and **throw a typed exception**:
  `Drupal\pokemon_api\Exception\PokeApiException`
- `PokemonRepository` does not catch this exception — it propagates to the sync layer,
  which decides whether to skip, retry, or fail the queue item
- Never return `NULL` from a repository method — throw instead

```php
// src/Exception/PokeApiException.php
final class PokeApiException extends \RuntimeException {}
```

---

## `pokemon_api_sync` — Drupal Sync Module

### Purpose

Consumes `pokemon_api` DTOs and persists them as Drupal entities. This module owns
all knowledge about Drupal content types, field names, and taxonomy vocabularies.

### Responsibilities

| Responsibility | Belongs here? |
|---|---|
| Enqueueing sync jobs (cron / Drush) | ✅ Yes |
| Queue worker processing | ✅ Yes |
| Creating / updating `pokemon` nodes | ✅ Yes |
| Creating / updating `pokemon_type` taxonomy terms | ✅ Yes |
| Field mapping from DTO → Drupal fields | ✅ Yes |
| Injecting `PokemonRepositoryInterface` | ✅ Yes |
| Making HTTP requests | ❌ No — `pokemon_api` |
| Knowing PokeAPI URL structure | ❌ No — `pokemon_api` |

### Drupal Entity Mapping

| `PokemonData` property | Drupal field | Notes |
|---|---|---|
| `$id` | `field_pokemon_id` (integer) | Pokédex number; used as lookup key |
| `$name` | `title` | Node title |
| `$height` | `field_pokemon_height` (integer) | Stored in decimetres as returned by API |
| `$weight` | `field_pokemon_weight` (integer) | Stored in hectograms as returned by API |
| `$types` | `field_pokemon_types` (entity ref) | References `pokemon_type` taxonomy terms |
| `$stats` | `field_pokemon_stats` (JSON / paragraph) | Stored as serialised array |
| `$spriteUrl` | `field_pokemon_sprite` (image or link) | Remote image URL |
| `PokemonSpeciesData::$flavorText` | `field_pokemon_flavor_text` (text) | English only |

**Looking up existing nodes before creating new ones:**

```php
// ✅ Always check for an existing node by Pokédex number before creating
$existing = $this->entityTypeManager
  ->getStorage('node')
  ->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'pokemon')
  ->condition('field_pokemon_id', $id)
  ->execute();

if ($existing) {
  $node = $this->entityTypeManager->getStorage('node')->load(reset($existing));
}
else {
  $node = Node::create(['type' => 'pokemon']);
}
```

### Taxonomy Term Upsert

Type terms must be created if they do not exist. Always look up before creating.

```php
// ✅ Term upsert pattern
private function upsertTypeTerm(string $typeName): int {
  $terms = $this->entityTypeManager
    ->getStorage('taxonomy_term')
    ->getQuery()
    ->accessCheck(FALSE)
    ->condition('vid', 'pokemon_type')
    ->condition('name', $typeName)
    ->execute();

  if ($terms) {
    return (int) reset($terms);
  }

  $term = Term::create([
    'vid' => 'pokemon_type',
    'name' => $typeName,
  ]);
  $term->save();
  return (int) $term->id();
}
```

### Queue Architecture

```
src/Plugin/QueueWorker/
└── PokemonSyncWorker.php   ← Processes one Pokémon per queue item
```

Queue item structure:

```php
// Item added to queue:
[
  'pokemon_id' => 25,   // int — Pokédex number
]
```

Worker responsibilities:
1. Call `PokemonRepositoryInterface::getPokemon($id)` and `getPokemonSpecies($id)`
2. Upsert taxonomy terms for each type
3. Upsert the `pokemon` node
4. Log success with `@channel pokemon_api_sync`
5. On `PokeApiException`: log the error and **re-throw** so the queue marks the item
   as failed and retries it. Do not silently swallow API errors.

```php
// ✅ Worker error handling
public function processItem(mixed $data): void {
  try {
    $pokemon = $this->repository->getPokemon((int) $data['pokemon_id']);
    $species = $this->repository->getPokemonSpecies((int) $data['pokemon_id']);
    $this->syncService->sync($pokemon, $species);
  }
  catch (PokeApiException $e) {
    $this->loggerFactory->get('pokemon_api_sync')
      ->error('Sync failed for Pokémon @id: @msg', [
        '@id' => $data['pokemon_id'],
        '@msg' => $e->getMessage(),
      ]);
    throw $e;  // ← re-throw so queue retries the item
  }
}
```

### Drush Commands

```
src/Commands/
└── PokemonSyncCommands.php
```

| Command | Option | Description |
|---|---|---|
| `pokemon:sync` | — | Enqueue all Pokémon (default 151, configurable) |
| `pokemon:sync` | `--id=N` | Enqueue a single Pokémon by Pokédex number |
| `pokemon:sync` | `--force` | Re-sync even if node already exists |
| `pokemon:clear-cache` | — | Invalidate the `pokemon_api` cache bin |

```bash
ddev drush pokemon:sync
ddev drush pokemon:sync --id=25
ddev drush pokemon:sync --force
ddev drush pokemon:clear-cache
ddev drush queue:run pokemon_api_sync
```

### Hook Classes

```
src/Hook/
└── PokemonSyncHooks.php    ← hook_cron to enqueue sync
```

```php
#[Hook('cron')]
public function cron(): void {
  // Only enqueue if the queue is empty to avoid duplication.
  $queue = $this->queueFactory->get('pokemon_api_sync');
  if ($queue->numberOfItems() === 0) {
    $this->syncCommands->enqueueAll();
  }
}
```

---

## Configuration

Both modules share a single settings object.

```yaml
# pokemon_api/config/install/pokemon_api.settings.yml
base_url: 'https://pokeapi.co/api/v2'
cache_ttl: 86400       # seconds; 24 hours
request_timeout: 10    # seconds per HTTP request

# pokemon_api_sync/config/install/pokemon_api_sync.settings.yml
sync_limit: 151        # how many Pokémon to enqueue per full sync
sync_language: 'en'    # language code used when filtering flavor text
```

Reading config:

```php
// In pokemon_api
$this->configFactory->get('pokemon_api.settings')->get('base_url');

// In pokemon_api_sync
$this->configFactory->get('pokemon_api_sync.settings')->get('sync_limit');
```

---

## File Structure

```
pokemon_api/
├── pokemon_api.info.yml
├── pokemon_api.services.yml          ← PokeApiClient, PokemonRepository, cache bin
├── pokemon_api.install                ← (empty unless schema needed)
├── config/
│   └── install/
│       └── pokemon_api.settings.yml
└── src/
    ├── DTO/
    │   ├── PokemonData.php
    │   ├── PokemonSpeciesData.php
    │   └── PokemonTypeData.php
    ├── Exception/
    │   └── PokeApiException.php
    ├── Service/
    │   ├── PokeApiClient.php           ← internal; HTTP only
    │   └── PokemonRepository.php       ← public API; returns DTOs
    └── PokemonRepositoryInterface.php  ← interface injected by pokemon_api_sync

pokemon_api_sync/
├── pokemon_api_sync.info.yml           ← depends: [pokemon_api]
├── pokemon_api_sync.services.yml       ← PokemonSyncService, Commands
├── pokemon_api_sync.install
├── config/
│   └── install/
│       └── pokemon_api_sync.settings.yml
└── src/
    ├── Commands/
    │   └── PokemonSyncCommands.php
    ├── Hook/
    │   └── PokemonSyncHooks.php        ← hook_cron
    ├── Plugin/
    │   └── QueueWorker/
    │       └── PokemonSyncWorker.php
    └── Service/
        └── PokemonSyncService.php      ← node/term upsert logic
```

---

## Testing

### `pokemon_api`

| Test | Type | What it covers |
|---|---|---|
| `PokeApiClientTest` | Unit | HTTP response parsing, exception on 4xx/5xx |
| `PokemonDataTest` | Unit | `fromApiResponse()` with valid and malformed fixtures |
| `PokemonRepositoryTest` | Kernel | Cache hit/miss, delegates to client |

Mock HTTP with Guzzle's `MockHandler` — never make real network calls in tests.

```php
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

$mock = new MockHandler([
  new Response(200, [], file_get_contents(__DIR__ . '/fixtures/pokemon-25.json')),
]);
$client = new Client(['handler' => HandlerStack::create($mock)]);
```

Store all API response fixtures as JSON files in `tests/fixtures/`.

### `pokemon_api_sync`

| Test | Type | What it covers |
|---|---|---|
| `PokemonSyncServiceTest` | Kernel | Node upsert, term upsert, idempotency |
| `PokemonSyncWorkerTest` | Kernel | Queue item processing, error re-throw |

Use a mock `PokemonRepositoryInterface` (not a real HTTP call) in sync tests.

---

## Guardrails

- **`pokemon_api` must never reference Drupal entity types, field names, or node storage**
- **`pokemon_api_sync` must never make HTTP calls directly** — only via `PokemonRepositoryInterface`
- **Never pass raw `array` API responses** across the module boundary — use DTOs
- **Never return `NULL` from repository methods** — throw `PokeApiException`
- **Never swallow `PokeApiException` in the queue worker** — re-throw so the item retries
- **Always upsert, never blindly create** — check for existing node by `field_pokemon_id`
- **Cache in `pokemon_api`, not in `pokemon_api_sync`** — caching is the client layer's concern