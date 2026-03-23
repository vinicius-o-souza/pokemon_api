<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Endpoints;

/**
 * Stat resource.
 */
class Stat extends TranslatableResource {

  /**
   * {@inheritdoc}
   */
  public static function getEndpoint(): string {
    return Endpoints::STAT->value;
  }

}
