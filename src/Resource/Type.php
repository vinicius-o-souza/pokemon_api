<?php

namespace Drupal\pokemon_api\Resource;

/**
 * Resource Type class.
 */
class Type extends TranslatableResource {

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
   * {@inheritdoc}
   */
  public static function createFromArray(array $data): Type {
    $type = new Type($data['name'], $data['url'] ?? NULL, $data['id'] ?? NULL);
    $type->setNames($data['names'] ?? []);

    return $type;
  }

}
