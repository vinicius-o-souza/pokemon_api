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
  private const ENDPOINT = 'stat';

  /**
   * The effect of this ability listed in different languages.
   *
   * @var \Drupal\pokemon_api\Translation
   */
  private Translation $effectEntries;

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
    $ability->setEffectEntries($data['effect_entries'] ?? []);

    return $ability;
  }

  /**
   * Get the effect of this ability listed in different languages.
   *
   * @return \Drupal\pokemon_api\Translation
   *   The effect of this ability listed in different languages.
   */
  public function getEffectEntries(): Translation {
    return $this->effectEntries;
  }

  /**
   * Set the effect of this ability listed in different languages.
   *
   * @param array $effectEntries
   *   The effect of this ability listed in different languages.
   */
  public function setEffectEntries(array $effectEntries): void {
    $this->effectEntries = new Translation($effectEntries);
  }

}
