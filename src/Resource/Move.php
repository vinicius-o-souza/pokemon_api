<?php

namespace Drupal\pokemon_api\Resource;

/**
 * Resource Move class.
 */
class Move extends TranslatableResource {

  /**
   * The endpoint.
   *
   * @var string
   */
  private const ENDPOINT = 'move';

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
  public static function createFromArray(array $data): Move {
    $move = new Move($data['name'], $data['url']);
    $move->setNames($data['names'] ?? []);

    return $move;
  }

}
