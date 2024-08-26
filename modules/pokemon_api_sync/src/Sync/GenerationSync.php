<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\pokemon_api\Endpoints;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api\Resource\Generation;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api_sync\SyncTermEntity;

/**
 * Sync Pokemon Generation taxonomy.
 */
class GenerationSync extends SyncTermEntity {

  /**
   * {@inheritdoc}
   */
  public function sync(int $limit = PokeApi::MAX_LIMIT, int $offset = 0): void {
    $generations = $this->pokeApi->getResources(Endpoints::GENERATION->value, $limit, $offset);

    foreach ($generations as $generation) {
      $this->syncResource($generation);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getVid(): string {
    return 'pokemon_generation';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTranslatableFields(ResourceInterface $resource): array {
    if (!$resource instanceof Generation) {
      return [];
    }

    return [
      'name' => $resource->getNames(),
      'field_pokeapi_id' => $resource->getId(),
    ];
  }

}
