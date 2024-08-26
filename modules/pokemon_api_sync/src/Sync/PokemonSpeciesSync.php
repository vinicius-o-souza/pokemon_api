<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\pokemon_api\Endpoints;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api\Resource\PokemonSpecies;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api_sync\SyncNodeEntity;

/**
 * Sync Pokemon node.
 */
class PokemonSpeciesSync extends SyncNodeEntity {

  /**
   * List of taxonomy terms needed.
   *
   * @var array
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
  public function sync(int $limit = PokeApi::MAX_LIMIT, int $offset = 0): void {
    if (empty($this->taxonomyTerms)) {
      $this->taxonomyTerms = $this->getAllTerms();
    }

    $pokemons = $this->pokeApi->getResources(Endpoints::POKEMON_SPECIES->value, $limit, $offset);

    foreach ($pokemons as $pokemon) {
      $this->syncResource($pokemon);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDataFields(ResourceInterface $resource, ?ContentEntityBase $node): array {
    if (!$resource instanceof PokemonSpecies) {
      throw new \Exception('Invalid resource type.');
    }

    $generation = $resource->getGeneration();
    $generation = $this->getTermsByApiIds('pokemon_generation', [$generation->getId() => $generation]);

    return [
      'type' => 'pokemon',
      'field_pokemon_mythical' => $resource->getIsMythical(),
      'field_pokemon_legendary' => $resource->getIsLegendary(),
      'field_pokemon_generation' => $generation,
    ];
  }

  /**
   * Get all taxonomy terms needed.
   *
   * @return array
   *   List of taxonomy terms needed.
   */
  private function getAllTerms(): array {
    $generations = $this->getTermsByVid('pokemon_generation');

    return [
      'pokemon_generation' => $generations,
    ];
  }

  /**
   * Get array of pokemon api IDs.
   *
   * @param string $vid
   *   The vid.
   * @param array $resourceApiIds
   *   The resource api ids.
   *
   * @return array
   *   List of pokemon types api IDs.
   */
  private function getTermsByApiIds(string $vid, array $resourceApiIds): array {
    $terms = [];
    foreach ($this->taxonomyTerms[$vid] as $pokeApiId => $term) {
      if (array_key_exists($pokeApiId, $resourceApiIds)) {
        $terms[] = $term;
      }
    }

    return $terms;
  }

}
