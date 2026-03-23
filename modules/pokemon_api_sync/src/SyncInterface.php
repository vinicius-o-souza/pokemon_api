<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync;

use Drupal\pokemon_api\PokeApiInterface;
use Drupal\pokemon_api\Resource\ResourceInterface;

/**
 * Interface for resource sync operations.
 */
interface SyncInterface {

  /**
   * Syncs resources from the PokeAPI.
   *
   * @param int $limit
   *   The number of resources to sync.
   * @param int $offset
   *   The offset to start from.
   */
  public function sync(int $limit = PokeApiInterface::MAX_LIMIT, int $offset = 0): void;

  /**
   * Syncs a single resource.
   *
   * @param \Drupal\pokemon_api\Resource\ResourceInterface $resource
   *   The resource to synchronize.
   */
  public function syncResource(ResourceInterface $resource): void;

}
