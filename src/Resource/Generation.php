<?php

namespace Drupal\pokemon_api\Resource;

/**
 * Resource Generation class.
 */
class Generation extends TranslatableResource {

  /**
   * The endpoint.
   *
   * @var string
   */
  private const ENDPOINT = 'generation';

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
  public static function createFromArray(array $data): Generation {
    $generation = new Generation($data['name'], $data['url'] ?? NULL, $data['id'] ?? NULL);
    $generation->setNames($data['names'] ?? []);

    return $generation;
  }

}
