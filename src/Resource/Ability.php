<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Endpoints;

/**
 * Ability resource.
 */
class Ability extends TranslatableResource {

  use FlavorTextTrait;

  /**
   * {@inheritdoc}
   */
  public static function getEndpoint(): string {
    return Endpoints::ABILITY->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromArray(array $data): static {
    $ability = parent::createFromArray($data);
    $ability->setFlavorText($data['flavor_text_entries'] ?? []);

    return $ability;
  }

}
