<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Endpoints;

/**
 * Generation resource.
 */
class Generation extends TranslatableResource {

  /**
   * {@inheritdoc}
   */
  public static function getEndpoint(): string {
    return Endpoints::GENERATION->value;
  }

}
