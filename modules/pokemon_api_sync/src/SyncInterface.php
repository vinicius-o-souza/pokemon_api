<?php

namespace Drupal\pokemon_api_sync;

use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api\Resource\ResourceInterface;

/**
 * Sync resources interface.
 */
interface SyncInterface {

  /**
   * Command to sync resources.
   *
   * @param int $limit
   *   The number of resources to sync.
   * @param int $offset
   *   The offset of resources to sync.
   */
  public function sync(int $limit = PokeApi::MAX_LIMIT, int $offset = 0): void;

  /**
   * Synchronizes a Resource object.
   *
   * @param \Drupal\pokemon_api\Resource\ResourceInterface $resource
   *   The resourve object to synchronize.
   */
  public function syncResource(ResourceInterface $resource): void;

}
