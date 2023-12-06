<?php

namespace Drupal\pokemon_api_sync;

use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Class NodeEntity.
 */
class NodeEntity extends Entity {

  /**
   * @inheritdoc
   */
  protected function getStorageClass(): EntityStorageInterface {
    return $this->entityTypeManager->getStorage('node');
  }
}