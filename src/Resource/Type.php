<?php

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Translation;

/**
 * Resource Type class.
 */
class Type extends Resource {

  /**
   * The names.
   *
   * @var \Drupal\pokemon_api\Translation|null
   */
  private Translation|null $names;

  /**
   * The endpoint.
   *
   * @var string
   */
  private const ENDPOINT = 'type';

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
   * Get the names.
   *
   * @return \Drupal\pokemon_api\Translation|null
   *   The names.
   */
  public function getNames(): ?Translation {
    return $this->names;
  }

  /**
   * Set the names.
   *
   * @param array $names
   *   The names.
   */
  public function setNames(array $names) {
    $this->names = new Translation($names);
  }

}
