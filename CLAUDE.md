# CLAUDE.md — `pokemon_api` Project

**For AI agents only.**

This file covers the architecture, responsibilities, data contracts, and guardrails specific to the `pokemon_api` and `pokemon_api_sync` modules. Read this before touching any file in either module.

> Coding standards and conventions → [DRUPAL_CODING_STANDARDS.md](DRUPAL_CODING_STANDARDS.md)

---

## Module Map

```
├── pokemon_api/           ← PokeAPI client: fetches, normalises
│   └── CLAUDE.md          ← This file
└── pokemon_api_sync/      ← Drupal sync: maps pokemon_api data into Drupal entities
```

**Strict dependency direction:**

```
pokemon_api_sync  →  depends on  →  pokemon_api
pokemon_api       →  knows nothing about  →  Drupal entities / nodes
```

- `pokemon_api` must never import or reference anything from `pokemon_api_sync`.
- `pokemon_api_sync` is the only layer allowed to create or update Drupal nodes and taxonomy terms.

---

## `pokemon_api` — PokeAPI Client Module

### Purpose

Provides a clean PHP interface to the PokeAPI REST API (`https://pokeapi.co/api/v2`).
Its only job is to **fetch and return structured data**. It has no opinion about how
that data is stored in Drupal.

### Resource Classes (`src/Resource/`)

Each class in `src/Resource/` represents a PokeAPI endpoint and acts as a DTO (Data Transfer Object). A Resource class encapsulates the endpoint path, handles the HTTP call via `PokeApi`, and exposes the response as typed properties. These are the only objects passed across the module boundary to `pokemon_api_sync` — never raw arrays.

### Responsibilities

| Responsibility | Belongs here? |
|---|---|
| HTTP requests to PokeAPI endpoints | Yes |
| Error handling and logging for HTTP failures | Yes |
| Creating `pokemon` nodes | No — `pokemon_api_sync` |
| Creating `pokemon_type` taxonomy terms | No — `pokemon_api_sync` |
| Knowing about Drupal content types or fields | No |

### Supported PokeAPI Endpoints

| Endpoint | Method | Purpose |
|---|---|---|
| `/pokemon?limit={n}&offset={o}` | `GET` | List Pokémon with pagination |
| `/pokemon-species?limit={n}&offset={o}` | `GET` | List Pokemon Species with pagination |
| `/type?limit={n}&offset={o}` | `GET` | List Type with pagination |
| `/stat?limit={n}&offset={o}` | `GET` | List Stat with pagination |
| `/ability?limit={n}&offset={o}` | `GET` | List Ability with pagination |
| `/generation?limit={n}&offset={o}` | `GET` | List Ability with pagination |

Update this table before calling any new endpoints.

### Error Handling

- All HTTP exceptions from Guzzle are caught in `PokeApiClient`.
- On failure, log with `@channel pokemon_api` and throw `Drupal\pokemon_api\Exception\PokeApiException`.
- Never return `NULL` from a repository method — throw instead.
- `PokeApiException` is intentionally minimal. Do not add methods or properties.

---

## `pokemon_api_sync` — Drupal Sync Module

### Purpose

Consumes `pokemon_api` DTOs and persists them as Drupal entities. This module owns
all knowledge about Drupal content types, field names, and taxonomy vocabularies.

### Responsibilities

| Responsibility | Belongs here? |
|---|---|
| Creating / updating `pokemon` nodes | Yes |
| Creating / updating `pokemon_type` taxonomy terms | Yes |
| Field mapping from DTO to Drupal fields | Yes |
| Making HTTP requests | No — `pokemon_api` |
| Knowing PokeAPI URL structure | No — `pokemon_api` |

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

### Node Upsert

Always look up existing nodes by `field_pokemon_id` before creating. See the upsert pattern in [DRUPAL_CODING_STANDARDS.md](DRUPAL_CODING_STANDARDS.md#9-entity-queries--database).

### Taxonomy Term Upsert

Type terms (`pokemon_type` vocabulary) must be looked up by `name` before creating. Never blindly create duplicates.

### Drush Commands

| Command | Description |
|---|---|
| `pokemon_api_sync:sync-pokemon` | Sync all Pokémon |

```bash
ddev drush pokemon_api_sync:sync-pokemon
```

---

## Configuration

Both modules share a single settings object.

```yaml
# pokemon_api/config/install/pokemon_api.settings.yml
base_url: 'https://pokeapi.co/api/v2'
```

```php
$this->configFactory->get('pokemon_api.settings')->get('base_url');
```

---

## File Structure

```
pokemon_api/
├── pokemon_api.info.yml
├── pokemon_api.services.yml          ← PokeApiClient
├── pokemon_api.install
├── config/
│   └── install/
│       └── pokemon_api.settings.yml
└── src/
    ├── Resource/
    │   └── Pokemon.php
    ├── Exception/
    │   └── PokeApiException.php
    └── PokeApi.php                    ← HTTP only

pokemon_api_sync/
├── pokemon_api_sync.info.yml          ← depends: [pokemon_api]
├── pokemon_api_sync.services.yml      ← PokemonSync, Commands
├── pokemon_api_sync.install
├── config/
│   └── install/
│       └── pokemon_api_sync.settings.yml
└── src/
    ├── Drush/Commands/
    │   └── PokemonSyncCommands.php
    ├── Hook/
    │   └── PokemonApiSyncHooks.php
    └── Service/
        └── PokemonSync.php            ← node/term upsert logic
```

---

## Testing

Store all API response fixtures as JSON files in `tests/fixtures/`.

---

## Guardrails

- **`pokemon_api` must never reference Drupal entity types, field names, or node storage.**
- **`pokemon_api_sync` must never make HTTP calls directly.**
- **Never pass raw `array` API responses across the module boundary** — use DTOs.
- **Never return `NULL` from repository methods** — throw `PokeApiException`.
- **Always upsert, never blindly create** — check for existing node by `field_pokemon_id`.
