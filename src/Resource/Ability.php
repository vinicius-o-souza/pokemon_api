<?php

namespace Drupal\pokemon_api\Resource;

/**
 * Resource Ability class.
 */
class Ability extends TranslatableResource {

  use FlavorTextTrait;

  /**
   * The endpoint.
   *
   * @var string
   */
  private const ENDPOINT = 'ability';

  /**
   * Get the endpoint.
   *
   * @return string
   *   The endpoint.
   */
  public static function getEndpoint(): string {
    return self::ENDPOINT;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromArray(array $data): Ability {
    $ability = new Ability($data['name'], $data['url'] ?? NULL, $data['id'] ?? NULL);
    $ability->setNames($data['names'] ?? []);
    $ability->setFlavorText($data['flavor_text_entries'] ?? []);

    return $ability;
  }

}
