<?php

namespace Drupal\pokemon_api_sync;

use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Sync taxonomy term entity.
 */
abstract class SyncTermEntity extends SyncEntity {

  /**
   * {@inheritdoc}
   */
  public function getStorageClass(): EntityStorageInterface {
    return $this->entityTypeManager->getStorage('taxonomy_term');
  }

}
