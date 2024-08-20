<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api\Resource\Type;
use Drupal\pokemon_api_sync\SyncInterface;
use Drupal\pokemon_api_sync\SyncTermEntity;

/**
 * Sync Pokemon Type taxonomy.
 */
class TypeSync extends SyncTermEntity implements SyncInterface {

  /**
   * {@inheritdoc}
   */
  public function syncAll(): void {
    $type = new Type();
    $types = $this->pokeApi->getAllResources($type);

    foreach ($types as $type) {
      $this->sync($type);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function syncPagination(int $limit, int $offset): void {
    $type = new Type();
    $types = $this->pokeApi->getResourcesPagination($type, $limit, $offset);

    foreach ($types as $type) {
      $this->sync($type);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function sync(ResourceInterface $type): void {
    $type = $this->pokeApi->getResource($type);

    if (!$type->getId()) {
      return;
    }

    $term = $this->readEntity($type->getId());
    $data = $this->getDataFields($type);

    if ($term) {
      $term = $this->updateEntity($term, $data);
    }
    else {
      $term = $this->createEntity($data);
    }

    if ($term && $type instanceof Type) {
      $translatableFields = $this->getTranslatableFields($type);
      $term = $this->addTranslation($term, $translatableFields);
      $term->save();
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
