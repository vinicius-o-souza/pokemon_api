<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Endpoints;

/**
 * Represents a Pokémon ability from the PokeAPI.
 */
class Ability extends TranslatableResource {

  use FlavorTextTrait;

  /**
   * {@inheritdoc}
   */
  public static function getEndpoint(): string {
    return Endpoints::Ability->value;
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
