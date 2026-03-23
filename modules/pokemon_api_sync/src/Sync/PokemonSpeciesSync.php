<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\pokemon_api\Endpoints;
use Drupal\pokemon_api\PokeApiInterface;
use Drupal\pokemon_api\Resource\PokemonSpecies;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api_sync\SyncNodeEntity;

/**
 * Syncs Pokémon species data to nodes.
 */
class PokemonSpeciesSync extends SyncNodeEntity {

  /**
   * Cached taxonomy terms keyed by vocabulary.
   *
   * @var array<string, array>
   */
  private array $taxonomyTerms = [];

  /**
   * {@inheritdoc}
   */
  public function getContentType(): string {
    return 'pokemon';
  }

  /**
   * {@inheritdoc}
   */
  public function sync(int $limit = PokeApiInterface::MAX_LIMIT, int $offset = 0): void {
    if (empty($this->taxonomyTerms)) {
      $this->taxonomyTerms = $this->loadAllTerms();
    }

    $pokemons = $this->pokeApi->getResources(Endpoints::PokemonSpecies->value, $limit, $offset);

    foreach ($pokemons as $pokemon) {
      $this->syncResource($pokemon);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDataFields(ResourceInterface $resource, ?ContentEntityBase $node): array {
    if (!$resource instanceof PokemonSpecies) {
      throw new \InvalidArgumentException('Expected PokemonSpecies resource.');
    }

    $generation = $resource->getGeneration();
    $generationTerms = $this->getTermsByApiIds('pokemon_generation', [$generation->getId() => $generation]);

    return [
      'type' => 'pokemon',
      'field_pokemon_mythical' => $resource->getIsMythical(),
      'field_pokemon_legendary' => $resource->getIsLegendary(),
      'field_pokemon_generation' => $generationTerms,
    ];
  }

  /**
   * Loads all required taxonomy terms.
   *
   * @return array<string, array>
   *   Terms keyed by vocabulary, then by PokeAPI ID.
   */
  private function loadAllTerms(): array {
    return [
      'pokemon_generation' => $this->getTermsByVid('pokemon_generation'),
    ];
  }

  /**
   * Filters cached terms by PokeAPI IDs.
   *
   * @param string $vid
   *   The vocabulary ID.
   * @param array $resourceApiIds
   *   Resource data keyed by PokeAPI ID.
   *
   * @return array
   *   Matching term IDs.
   */
  private function getTermsByApiIds(string $vid, array $resourceApiIds): array {
    $terms = [];
    foreach ($this->taxonomyTerms[$vid] as $pokeApiId => $termId) {
      if (array_key_exists($pokeApiId, $resourceApiIds)) {
        $terms[] = $termId;
      }
    }

    return $terms;
  }

}
