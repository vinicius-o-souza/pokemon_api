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
 * Represents a single Pokémon from the PokeAPI /pokemon/{id} endpoint.
 *
 * This is a readonly value object. Use PokemonData::fromApiResponse()
 * to construct from a raw API response array.
 */
final class PokemonData {

  /**
   * Constructs a PokemonData value object.
   *
   * @param int $id
   *   The Pokédex number.
   * @param string $name
   *   The Pokémon name as returned by the API.
   * @param int $height
   *   Height in decimetres as returned by the API.
   * @param int $weight
   *   Weight in hectograms as returned by the API.
   * @param string[] $types
   *   Type names in slot order, e.g. ['fire', 'flying'].
   * @param array<string, int> $stats
   *   Stat name keyed to base value, e.g. ['hp' => 45, 'attack' => 49].
   * @param string $spriteUrl
   *   URL to the default front sprite image.
   */
  public function __construct(
    public readonly int $id,
    public readonly string $name,
    public readonly int $height,
    public readonly int $weight,
    public readonly array $types,
    public readonly array $stats,
    public readonly string $spriteUrl,
  ) {}

  /**
   * Creates a PokemonData instance from a raw /pokemon/{id} API response.
   *
   * @param array<string, mixed> $data
   *   The decoded JSON response from the PokeAPI /pokemon/{id} endpoint.
   *
   * @return self
   *   A new PokemonData value object.
   */
  public static function fromApiResponse(array $data): self {
    $types = array_map(
      static fn(array $t): string => (string) $t['type']['name'],
      (array) $data['types'],
    );

    $statsRaw = array_map(
      static fn(array $s): array => [
        'name' => (string) $s['stat']['name'],
        'value' => (int) $s['base_stat'],
      ],
      (array) $data['stats'],
    );

    /** @var array<string, int> $stats */
    $stats = array_column($statsRaw, 'value', 'name');

    return new self(
      id: (int) $data['id'],
      name: (string) $data['name'],
      height: (int) $data['height'],
      weight: (int) $data['weight'],
      types: $types,
      stats: $stats,
      spriteUrl: (string) ($data['sprites']['front_default'] ?? ''),
    );
  }

}
```

**Key changes from a naive implementation:**
- All constructor properties must declare `readonly` explicitly (required in PHP 8.1 when not using the `readonly class` modifier)
- All closures passed to `array_map` must be typed with parameter and return types
- PHPStan requires `@var` annotation when `array_column()` returns a mixed-key array
- `array_map` callbacks accessing only their own parameters should be declared `static` to avoid accidental `$this` binding (PHPStan strict mode)

### Services

```
src/Service/
├── PokeApiClient.php       ← All HTTP calls; returns raw arrays or throws
└── PokemonRepository.php   ← Public API of this module; returns DTOs; owns caching
```

**`PokeApiClient`** is internal — only `PokemonRepository` calls it.
**`PokemonRepository`** is the only class `pokemon_api_sync` is allowed to inject.

```php
<?php

declare(strict_types=1);

namespace Drupal\pokemon_api;

use Drupal\pokemon_api\DTO\PokemonData;
use Drupal\pokemon_api\DTO\PokemonSpeciesData;

/**
 * Defines the public API for fetching Pokémon data from the PokeAPI.
 *
 * pokemon_api_sync must inject this interface, never the concrete class.
 */
interface PokemonRepositoryInterface {

  /**
   * Fetches a single Pokémon by Pokédex number.
   *
   * @param int $id
   *   The Pokédex number.
   *
   * @return \Drupal\pokemon_api\DTO\PokemonData
   *   A populated PokemonData DTO.
   *
   * @throws \Drupal\pokemon_api\Exception\PokeApiException
   *   Thrown if the HTTP request fails.
   */
  public function getPokemon(int $id): PokemonData;

  /**
   * Fetches species data for a Pokémon by Pokédex number.
   *
   * @param int $id
   *   The Pokédex number.
   *
   * @return \Drupal\pokemon_api\DTO\PokemonSpeciesData
   *   A populated PokemonSpeciesData DTO.
   *
   * @throws \Drupal\pokemon_api\Exception\PokeApiException
   *   Thrown if the HTTP request fails.
   */
  public function getPokemonSpecies(int $id): PokemonSpeciesData;

  /**
   * Lists Pokémon with pagination.
   *
   * @param int $limit
   *   The number of results to return.
   * @param int $offset
   *   The offset into the full list.
   *
   * @return \Drupal\pokemon_api\DTO\PokemonData[]
   *   An array of PokemonData DTOs.
   *
   * @throws \Drupal\pokemon_api\Exception\PokeApiException
   *   Thrown if the HTTP request fails.
   */
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
/**
 * Fetches a single Pokémon by Pokédex number, with caching.
 *
 * Results are cached for 24 hours in the pokemon_api cache bin.
 *
 * @param int $id
 *   The Pokédex number.
 *
 * @return \Drupal\pokemon_api\DTO\PokemonData
 *   A populated PokemonData DTO.
 *
 * @throws \Drupal\pokemon_api\Exception\PokeApiException
 *   Thrown if the HTTP request fails or returns an error status.
 */
public function getPokemon(int $id): PokemonData {
  $cid = "pokemon_api:pokemon:{$id}";
  $cached = $this->cache->get($cid);

  if ($cached !== FALSE) {
    // @phpstan-ignore-next-line Return type of CacheBackendInterface::get() is object|false; the data property is mixed.
    return $cached->data;
  }

  $raw = $this->client->fetchPokemon($id);
  $dto = PokemonData::fromApiResponse($raw);
  $this->cache->set($cid, $dto, (int) (time() + 86400));

  return $dto;
}
```

**Cache check note:** `CacheBackendInterface::get()` returns `object|false`, not `null`. Always compare with `!== FALSE` — a truthiness check (`if ($cached)`) can mask a valid cache entry whose `data` property evaluates to falsy.

### Error Handling

- All HTTP exceptions from Guzzle are caught in `PokeApiClient`
- On failure, log with `@channel pokemon_api` and **throw a typed exception**:
  `Drupal\pokemon_api\Exception\PokeApiException`
- `PokemonRepository` does not catch this exception — it propagates to the sync layer,
  which decides whether to skip, retry, or fail the queue item
- Never return `NULL` from a repository method — throw instead

```php
<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Exception;

/**
 * Thrown when a PokeAPI HTTP request fails.
 *
 * Callers that catch this exception should either retry the operation or
 * mark the related queue item as failed.
 */
final class PokeApiException extends \RuntimeException {}
```

**PHPMetrics note:** `PokeApiException` is intentionally minimal. Keep it that way — adding methods or properties raises cyclomatic complexity for no benefit.

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
/**
 * Loads an existing pokemon node by Pokédex number, or creates a new one.
 *
 * @param int $id
 *   The Pokédex number to look up.
 *
 * @return \Drupal\node\NodeInterface
 *   An existing or newly created (unsaved) pokemon node.
 */
private function loadOrCreatePokemonNode(int $id): \Drupal\node\NodeInterface {
  $storage = $this->entityTypeManager->getStorage('node');

  $existing = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'pokemon')
    ->condition('field_pokemon_id', $id)
    ->execute();

  if (!empty($existing)) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $storage->load((int) reset($existing));
    return $node;
  }

  /** @var \Drupal\node\NodeInterface $node */
  $node = $storage->create(['type' => 'pokemon']);
  return $node;
}
```

**PHPStan notes for entity queries:**
- `EntityStorageInterface::getQuery()` returns `\Drupal\Core\Entity\Query\QueryInterface`; chain calls are fine, but the final `execute()` returns `int[]|string[]` — cast values with `(int)` before passing to `load()`.
- `EntityStorageInterface::load()` returns `\Drupal\Core\Entity\EntityInterface|null`. Always add a `@var` docblock annotation or a `assert()` call so PHPStan narrows the type correctly.
- Never use `Node::create()` directly in a service — always go through `$this->entityTypeManager->getStorage('node')->create(...)` so the dependency is injectable and testable.

### Taxonomy Term Upsert

Type terms must be created if they do not exist. Always look up before creating.

```php
/**
 * Returns the term ID for a pokemon_type term, creating it if necessary.
 *
 * @param string $typeName
 *   The machine-readable type name from the PokeAPI (e.g. 'fire', 'flying').
 *
 * @return int
 *   The taxonomy term ID.
 */
private function upsertTypeTerm(string $typeName): int {
  $storage = $this->entityTypeManager->getStorage('taxonomy_term');

  $existing = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('vid', 'pokemon_type')
    ->condition('name', $typeName)
    ->execute();

  if (!empty($existing)) {
    return (int) reset($existing);
  }

  /** @var \Drupal\taxonomy\TermInterface $term */
  $term = $storage->create([
    'vid' => 'pokemon_type',
    'name' => $typeName,
  ]);
  $term->save();

  return (int) $term->id();
}
```

**PHPStan notes for taxonomy storage:**
- `TermStorageInterface::create()` returns `\Drupal\Core\Entity\EntityInterface`. Annotate with `@var \Drupal\taxonomy\TermInterface` so PHPStan resolves `->id()` correctly.
- `TermInterface::id()` returns `int|string|null`. Cast the return value to `(int)` — do not rely on loose comparison.
- Avoid chaining `->create()->save()` in one expression; split into two statements so PHPStan can resolve the intermediate type.

### Queue Architecture

```
src/Plugin/QueueWorker/
└── PokemonSyncWorker.php   ← Processes one Pokémon per queue item
```

Queue item structure:

```php
// Item added to queue — always pass a typed shape, never a bare value:
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
<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\Attribute\QueueWorker;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\pokemon_api\Exception\PokeApiException;
use Drupal\pokemon_api\PokemonRepositoryInterface;
use Drupal\pokemon_api_sync\Service\PokemonSyncServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes a single Pokémon sync queue item.
 */
#[QueueWorker(
  id: 'pokemon_api_sync',
  title: new TranslatableMarkup('Pokémon API sync'),
  cron: ['time' => 30],
)]
final class PokemonSyncWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a PokemonSyncWorker.
   *
   * @param array<string, mixed> $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\pokemon_api\PokemonRepositoryInterface $repository
   *   The Pokémon repository service.
   * @param \Drupal\pokemon_api_sync\Service\PokemonSyncServiceInterface $syncService
   *   The sync service responsible for node and term upserts.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger channel factory.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    mixed $plugin_definition,
    private readonly PokemonRepositoryInterface $repository,
    private readonly PokemonSyncServiceInterface $syncService,
    private readonly LoggerChannelFactoryInterface $loggerFactory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('pokemon_api.repository'),
      $container->get('pokemon_api_sync.sync_service'),
      $container->get('logger.factory'),
    );
  }

  /**
   * {@inheritdoc}
   *
   * @param mixed $data
   *   Expected shape: ['pokemon_id' => int].
   *
   * @throws \Drupal\pokemon_api\Exception\PokeApiException
   *   Re-thrown so the queue marks the item as failed and retries it.
   */
  public function processItem(mixed $data): void {
    $pokemonId = (int) $data['pokemon_id'];

    try {
      $pokemon = $this->repository->getPokemon($pokemonId);
      $species = $this->repository->getPokemonSpecies($pokemonId);
      $this->syncService->sync($pokemon, $species);
    }
    catch (PokeApiException $e) {
      $this->loggerFactory->get('pokemon_api_sync')->error(
        'Sync failed for Pokémon @id: @msg',
        ['@id' => $pokemonId, '@msg' => $e->getMessage()],
      );
      // Re-throw so the queue marks the item as failed and schedules a retry.
      throw $e;
    }
  }

}
```

**PHPCS / PHPStan notes for queue workers:**
- `QueueWorkerBase::__construct()` requires `array $configuration, $plugin_id, $plugin_definition` — always pass all three to `parent::__construct()`.
- Declare `ContainerFactoryPluginInterface` and implement `create()` so Drupal can inject services. Without it, the worker is instantiated without DI.
- `processItem()` accepts `mixed $data` in the base class — cast to concrete types (`(int)`, `(string)`) immediately inside the method body before use.
- Catch only the specific exception type (`PokeApiException`), never bare `\Exception` or `\Throwable`, unless you are re-throwing unconditionally.
- PHPMetrics flags methods with high cyclomatic complexity. Keep `processItem()` to a single try/catch block; delegate all business logic to `PokemonSyncServiceInterface::sync()`.
- The `#[QueueWorker]` attribute replaces the `@QueueWorker` docblock annotation entirely. The class docblock should contain only a human-readable description — no plugin metadata.

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
<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Queue\QueueFactory;
use Drupal\pokemon_api_sync\Commands\PokemonSyncCommands;

/**
 * Hook implementations for the pokemon_api_sync module.
 */
final class PokemonSyncHooks {

  /**
   * Constructs PokemonSyncHooks.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   The queue factory service.
   * @param \Drupal\pokemon_api_sync\Commands\PokemonSyncCommands $syncCommands
   *   The Drush sync commands service.
   */
  public function __construct(
    private readonly QueueFactory $queueFactory,
    private readonly PokemonSyncCommands $syncCommands,
  ) {}

  /**
   * Implements hook_cron().
   *
   * Enqueues a full Pokémon sync if the queue is currently empty.
   * This prevents duplicate items from accumulating across cron runs.
   */
  #[Hook('cron')]
  public function cron(): void {
    $queue = $this->queueFactory->get('pokemon_api_sync');

    if ($queue->numberOfItems() === 0) {
      $this->syncCommands->enqueueAll();
    }
  }

}
```

**PHPCS notes for hook classes:**
- Class-level docblock is required by the Drupal coding standard.
- All constructor parameters used only via `$this->` must be declared as constructor-promoted `private readonly` properties.
- Method docblocks must describe the hook being implemented (e.g. `Implements hook_cron().`).

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
        ├── PokemonSyncService.php      ← node/term upsert logic
        └── PokemonSyncServiceInterface.php
```

Note the addition of `PokemonSyncServiceInterface.php`. Always program to an interface, even for internal services — this keeps PHPStan happy and makes kernel tests straightforward to mock.

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
<?php

declare(strict_types=1);

namespace Drupal\Tests\pokemon_api\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\pokemon_api\Service\PokeApiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests PokeApiClient HTTP response handling.
 */
#[CoversClass(PokeApiClient::class)]
#[Group('pokemon_api')]
final class PokeApiClientTest extends UnitTestCase {

  /**
   * Creates a Guzzle client backed by a mock handler.
   *
   * @param \GuzzleHttp\Psr7\Response[] $responses
   *   The queue of responses to return in order.
   *
   * @return \GuzzleHttp\Client
   *   A configured Guzzle client.
   */
  private function createMockClient(array $responses): Client {
    $mock = new MockHandler($responses);
    return new Client(['handler' => HandlerStack::create($mock)]);
  }

  /**
   * Tests that fetchPokemon() returns a parsed array on a 200 response.
   */
  #[CoversMethod(PokeApiClient::class, 'fetchPokemon')]
  public function testFetchPokemonReturnsParsedArray(): void {
    $fixture = (string) file_get_contents(__DIR__ . '/fixtures/pokemon-25.json');
    $client = $this->createMockClient([
      new Response(200, [], $fixture),
    ]);
    // ... assert against $client
  }

}
```

**PHPCS notes for tests:**
- Test classes must have a class-level docblock; `@group` moves to `#[Group('pokemon_api')]` above the class.
- `#[CoversClass(Foo::class)]` replaces `@coversDefaultClass \Fully\Qualified\Foo` — use `::class` constant, no string.
- `#[CoversMethod(Foo::class, 'methodName')]` replaces per-method `@covers ::methodName` — must reference the class explicitly.
- Helper methods must be `private` if only used within the test class; they still require a docblock.
- `file_get_contents()` returns `string|false`; cast to `(string)` or add an assertion before use to avoid PHPStan errors.

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

---

## Code Quality Rules

These rules enforce compliance with `phpcs --standard=Drupal,DrupalPractice`, `phpstan analyze`, and `phpmetrics`.

### PHPCS — Drupal & DrupalPractice Standard

| Rule | Requirement |
|---|---|
| File header | Every PHP file must open with `<?php`, a blank line, then `declare(strict_types=1);` |
| Docblocks | Every class, interface, method, and property requires a docblock. The first line is a one-sentence summary ending with a full stop. Parameter and return tags must use fully-qualified class names prefixed with `\`. |
| `{@inheritdoc}` | Use `{@inheritdoc}` only on methods that add no new information. If you override behaviour, write a full docblock. |
| Trailing commas | Use trailing commas in all multi-line arrays and argument lists. |
| Short closures | Prefer named `static fn` closures over anonymous `function` closures for `array_map` / `array_filter` callbacks. |
| `TRUE` / `FALSE` / `NULL` | Always uppercase in non-type-hint positions (e.g. `return NULL;`, `=== FALSE`). |
| String concatenation | Add a space on both sides of the `.` operator. |
| Blank lines | One blank line between method definitions; no blank line after the opening brace. |
| File endings | All PHP files must end with a newline. |
| Plugin annotations | **Never use docblock annotations for plugins** (`@QueueWorker`, `@Block`, `@Field*`, etc.). Use PHP attributes instead — see the PHP Attributes section below. |

### PHP Attributes

Drupal 10.2+ supports PHP 8 attributes as the canonical way to declare plugins and hooks. **Always use attributes over docblock annotations.** Docblock annotations (`@QueueWorker`, `@Block`, `@Translation`, `@Hook`, etc.) are deprecated and will be removed in a future Drupal major version.

#### Plugin attributes

| Docblock annotation (❌ deprecated) | PHP attribute (✅ required) | Import |
|---|---|---|
| `@QueueWorker(id="...", title=@Translation("..."), cron={"time"=30})` | `#[QueueWorker(id: '...', title: new TranslatableMarkup('...'), cron: ['time' => 30])]` | `Drupal\Core\Queue\Attribute\QueueWorker` |
| `@Block(id="...", admin_label=@Translation("..."))` | `#[Block(id: '...', admin_label: new TranslatableMarkup('...'))]` | `Drupal\Core\Block\Attribute\Block` |
| `@FieldType(id="...", label=@Translation("..."))` | `#[FieldType(id: '...', label: new TranslatableMarkup('...'))]` | `Drupal\Core\Field\Attribute\FieldType` |
| `@FieldWidget(id="...", label=@Translation("..."))` | `#[FieldWidget(id: '...', label: new TranslatableMarkup('...'))]` | `Drupal\Core\Field\Attribute\FieldWidget` |
| `@FieldFormatter(id="...", label=@Translation("..."))` | `#[FieldFormatter(id: '...', label: new TranslatableMarkup('...'))]` | `Drupal\Core\Field\Attribute\FieldFormatter` |

**Rules for plugin attributes:**

- Place the attribute immediately before the `class` keyword, after the docblock.
- Use named argument syntax (`id: '...'`, not positional).
- Replace every `@Translation("...")` with `new TranslatableMarkup('...')` — import `Drupal\Core\StringTranslation\TranslatableMarkup`.
- Replace `{"key" = value}` annotation arrays with `['key' => value]` PHP arrays.
- The class docblock must contain only a human-readable description of the plugin — no plugin metadata.

```php
// ❌ Deprecated — docblock annotation
/**
 * Processes a single Pokémon sync queue item.
 *
 * @QueueWorker(
 *   id = "pokemon_api_sync",
 *   title = @Translation("Pokémon API sync"),
 *   cron = {"time" = 30}
 * )
 */
final class PokemonSyncWorker extends QueueWorkerBase {}

// ✅ Correct — PHP attribute
use Drupal\Core\Queue\Attribute\QueueWorker;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Processes a single Pokémon sync queue item.
 */
#[QueueWorker(
  id: 'pokemon_api_sync',
  title: new TranslatableMarkup('Pokémon API sync'),
  cron: ['time' => 30],
)]
final class PokemonSyncWorker extends QueueWorkerBase {}
```

#### Hook attributes

Hooks are declared with `#[Hook('hook_name')]` on methods inside a dedicated hook class. This replaces both procedural `hook_*()` functions in `.module` files and `@Hook` docblock annotations.

```php
// ❌ Procedural hook in .module file (avoid for new code)
function pokemon_api_sync_cron(): void { ... }

// ✅ Correct — attribute-based hook in a hook class
use Drupal\Core\Hook\Attribute\Hook;

#[Hook('cron')]
public function cron(): void { ... }
```

- One hook class per module, in `src/Hook/`.
- The class must be a plain service — no base class required.
- Register it in `*.services.yml` with the module's service container.
- The method name does not need to match the hook name, but using the hook name as the method name is conventional and keeps the code readable.

#### PHPUnit attributes

PHPUnit 10+ (required by Drupal 11) uses PHP attributes instead of docblock annotations for test metadata.

| Docblock annotation (❌ deprecated) | PHP attribute (✅ required) | Import |
|---|---|---|
| `@group my_module` on the class | `#[Group('my_module')]` on the class | `PHPUnit\Framework\Attributes\Group` |
| `@coversDefaultClass \Foo\Bar` on the class | `#[CoversClass(Bar::class)]` on the class | `PHPUnit\Framework\Attributes\CoversClass` |
| `@covers ::methodName` on a method | `#[CoversMethod(Bar::class, 'methodName')]` on the method | `PHPUnit\Framework\Attributes\CoversMethod` |
| `@dataProvider providerName` on a method | `#[DataProvider('providerName')]` on the method | `PHPUnit\Framework\Attributes\DataProvider` |

**Rules for PHPUnit attributes:**

- `#[CoversClass(Foo::class)]` uses the `::class` constant — never a string.
- `#[CoversMethod(Foo::class, 'method')]` must explicitly name the class, unlike `@covers ::method` which relied on `@coversDefaultClass`.
- Remove `@coversDefaultClass` entirely — `#[CoversClass]` replaces it.
- `#[Group]` and `#[CoversClass]` go on the class; `#[CoversMethod]` and `#[DataProvider]` go on individual test methods.

```php
// ❌ Deprecated — docblock annotations
/**
 * Tests PokeApiClient HTTP response handling.
 *
 * @group pokemon_api
 * @coversDefaultClass \Drupal\pokemon_api\Service\PokeApiClient
 */
final class PokeApiClientTest extends UnitTestCase {

  /**
   * @covers ::fetchPokemon
   */
  public function testFetchPokemon(): void { ... }
}

// ✅ Correct — PHP attributes
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests PokeApiClient HTTP response handling.
 */
#[CoversClass(PokeApiClient::class)]
#[Group('pokemon_api')]
final class PokeApiClientTest extends UnitTestCase {

  #[CoversMethod(PokeApiClient::class, 'fetchPokemon')]
  public function testFetchPokemon(): void { ... }
}
```

### PHPStan

| Rule | Requirement |
|---|---|
| Return types | Every method must declare a return type, including `void`. No `mixed` returns unless unavoidable. |
| Typed properties | All class properties must be typed. Constructor-promoted properties inherit the type from the parameter. |
| Nullable types | Use `?Type` syntax, not `Type\|null` in signatures, for single-nullable parameters. |
| Array shapes | Annotate complex arrays with `@param array<KeyType, ValueType>` or `@var` docblocks so PHPStan can narrow types. |
| Null-safe calls | Use `$obj?->method()` where a value may be `null` instead of inline conditionals — but never chain null-safe calls when the intermediate could be non-null by contract. |
| Entity type narrowing | Always add `@var \Drupal\...\SomeInterface` after `EntityStorageInterface::load()` or `EntityStorageInterface::create()` calls — these return `EntityInterface\|null` and `EntityInterface` respectively, which PHPStan cannot narrow automatically. |
| `CacheBackendInterface::get()` | Returns `object\|false`, not `?\stdClass`. Compare with `!== FALSE`, not `!= NULL` or a bare truthiness check. |

### PHPMetrics — Avoiding Violations

PHPMetrics flags classes that exceed maintainability thresholds. Follow these structural rules to stay clean:

| Metric | Limit | How to comply |
|---|---|---|
| Cyclomatic complexity per method | ≤ 5 | Extract nested conditionals into private helper methods with descriptive names. |
| Lines of code per method | ≤ 20 | If a method grows beyond 20 lines, extract a helper or introduce a value object. |
| Number of methods per class | ≤ 10 | Split large service classes into focused collaborators (e.g. separate `NodeUpsertService` from `TermUpsertService`). |
| Coupling between objects | Low | Depend on interfaces, not concrete classes. Avoid `new ClassName()` inside services — use DI. |
| Lack of cohesion (LCOM) | Low | A class should have one reason to change. If two groups of methods share no properties, split the class. |
| Abstract / concrete ratio | ≥ 0.2 | Every concrete service should implement a corresponding interface. |

**Structural pattern to follow:**

```
PokemonSyncService          ← orchestrates; ≤ 5 methods
├── NodeUpsertHelper        ← private collaborator or extracted private methods
└── TermUpsertHelper        ← private collaborator or extracted private methods
```

Rather than one large class handling both node and term logic, keep each responsibility in a focused class or a small group of private helper methods. This keeps cyclomatic complexity and method count within PHPMetrics thresholds while also improving testability.
