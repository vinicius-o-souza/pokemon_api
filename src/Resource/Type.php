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
  public function createFromArray(array $data): Type {
    $type = new Type($data['name'], $data['url']);
    $type->setNames($data['names'] ?? []);

    return $type;
  }

}
