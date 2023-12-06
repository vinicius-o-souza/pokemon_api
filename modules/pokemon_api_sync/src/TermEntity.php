<?php

namespace Drupal\pokemon_api_sync;

use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Class TermEntity.
 */
class TermEntity extends Entity {

  /**
   * @inheritdoc
   */
  protected function getStorageClass(): EntityStorageInterface {
    return $this->entityTypeManager->getStorage('taxonomy_term');
  }
}