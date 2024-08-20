<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api\Resource\Stat;
use Drupal\pokemon_api_sync\SyncInterface;
use Drupal\pokemon_api_sync\SyncTermEntity;

/**
 * Sync Pokemon Stat taxonomy.
 */
class StatSync extends SyncTermEntity implements SyncInterface {

  /**
   * {@inheritdoc}
   */
  public function syncAll(): void {
    $stat = new Stat();
    $stats = $this->pokeApi->getAllResources($stat);

    foreach ($stats as $stat) {
      $this->sync($stat);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getVid(): string {
    return 'pokemon_stat';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTranslatableFields(ResourceInterface $resource): array {
    if (!$resource instanceof Stat) {
      return [];
    }
    return [
      'name' => $resource->getNames(),
    ];
  }

}
