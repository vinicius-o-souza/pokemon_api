<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\pokemon_api\Endpoints;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api\Resource\Type;
use Drupal\pokemon_api_sync\SyncTermEntity;

/**
 * Sync Pokemon Type taxonomy.
 */
class TypeSync extends SyncTermEntity {

  /**
   * {@inheritdoc}
   */
  public function sync(int $limit = PokeApi::MAX_LIMIT, int $offset = 0): void {
    $types = $this->pokeApi->getResources(Endpoints::TYPE->value, $limit, $offset);

    foreach ($types as $type) {
      $this->syncResource($type);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getVid(): string {
    return 'pokemon_type';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTranslatableFields(ResourceInterface $resource): array {
    if (!$resource instanceof Type) {
      return [];
    }
    return [
      'name' => $resource->getNames(),
    ];
  }

}
