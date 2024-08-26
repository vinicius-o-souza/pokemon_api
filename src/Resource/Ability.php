<?php

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Endpoints;

/**
 * Resource Ability class.
 */
class Ability extends TranslatableResource {

  use FlavorTextTrait;

  /**
   * Get the endpoint.
   *
   * @return string
   *   The endpoint.
   */
  public static function getEndpoint(): string {
    return Endpoints::ABILITY->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromArray(array $data): Ability {
    $ability = parent::createFromArray($data);
    $ability->setNames($data['names'] ?? []);
    $ability->setFlavorText($data['flavor_text_entries'] ?? []);

    return $ability;
  }

}
