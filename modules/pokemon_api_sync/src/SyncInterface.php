<?php

namespace Drupal\pokemon_api_sync;

use Drupal\pokemon_api\Resource\ResourceInterface;

/**
 * Sync resources interface.
 */
interface SyncInterface {

  /**
   * Command to sync all resources.
   */
  public function syncAll(): void;

  /**
   * Synchronizes a Resource object.
   *
   * @param \Drupal\pokemon_api\Resource\ResourceInterface $resource
   *   The resourve object to synchronize.
   */
  public function sync(ResourceInterface $resource): void;

}
