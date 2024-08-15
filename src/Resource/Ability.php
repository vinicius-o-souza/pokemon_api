<?php

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Translation;

/**
 * Resource Ability class.
 */
class Ability extends TranslatableResource {

  /**
   * The endpoint.
   *
   * @var string
   */
  private const ENDPOINT = 'ability';

  /**
   * The flavor text of this ability listed in different languages.
   *
   * @var \Drupal\pokemon_api\Translation
   */
  private Translation $flavorText;

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

  /**
   * Get the flavor text of this ability listed in different languages.
   *
   * @return \Drupal\pokemon_api\Translation
   *   The flavor text of this ability listed in different languages.
   */
  public function getFlavorText(): Translation {
    return $this->flavorText;
  }

  /**
   * Set the flavor text of this ability listed in different languages.
   *
   * @param array $flavorText
   *   The flavor text of this ability listed in different languages.
   */
  public function setFlavorText(array $flavorText): void {
    $this->flavorText = new Translation($flavorText, 'flavor_text');
  }

}
