<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\pokemon_api\Resource\Ability;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api_sync\SyncInterface;
use Drupal\pokemon_api_sync\SyncTermEntity;

/**
 * Sync Pokemon Ability taxonomy.
 */
class AbilitySync extends SyncTermEntity implements SyncInterface {

  /**
   * {@inheritdoc}
   */
  public function syncAll(): void {
    $ability = new Ability();
    $abilities = $this->pokeApi->getAllResources($ability);

    foreach ($abilities as $ability) {
      $this->sync($ability);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function syncPagination(int $limit, int $offset): void {
    $ability = new Ability();
    $abilities = $this->pokeApi->getResourcesPagination($ability, $limit, $offset);

    foreach ($abilities as $ability) {
      $this->sync($ability);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getVid(): string {
    return 'pokemon_ability';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTranslatableFields(ResourceInterface $resource): array {
    if (!$resource instanceof Ability) {
      return [];
    }
    return [
      'name' => $resource->getNames(),
      'description' => $resource->getFlavorText(),
    ];
  }

}
