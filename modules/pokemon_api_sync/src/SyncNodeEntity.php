<?php

namespace Drupal\pokemon_api_sync;

use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Sync node entity.
 */
abstract class SyncNodeEntity extends SyncEntity {

  /**
   * {@inheritdoc}
   */
  public function getStorageClass(): EntityStorageInterface {
    return $this->entityTypeManager->getStorage('node');
  }

}
