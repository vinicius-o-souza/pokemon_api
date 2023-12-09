<?php

namespace Drupal\pokemon_api_sync\Resource;

use Drupal\pokemon_api_sync\ResourceInterface;

/**
 * Resource Type pokemon_type taxonomy.
 */
class Type implements ResourceInterface {

  /**
   * {@inheritdoc}
   */
  public function getEntityType(): string {
    return 'taxonomy_term';
  }

  /**
   * {@inheritdoc}
   */
  public function getBundle(): string {
    return 'pokemon_type';
  }

}
