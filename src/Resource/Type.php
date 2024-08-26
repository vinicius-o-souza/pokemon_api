<?php

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Endpoints;

/**
 * Resource Type class.
 */
class Type extends TranslatableResource {

  /**
   * Get the endpoint.
   *
   * @return string
   *   The endpoint.
   */
  public static function getEndpoint(): string {
    return Endpoints::TYPE->value;
  }

}
