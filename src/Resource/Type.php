<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Endpoints;

/**
 * Represents a Pokémon type from the PokeAPI.
 */
class Type extends TranslatableResource {

  /**
   * {@inheritdoc}
   */
  public static function getEndpoint(): string {
    return Endpoints::Type->value;
  }

}
