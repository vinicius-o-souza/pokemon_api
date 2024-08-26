<?php

namespace Drupal\pokemon_api;

use Drupal\pokemon_api\Resource\ResourceInterface;

/**
 * Interface for PokeApiInterface.
 */
interface PokeApiInterface {

  /**
   * The limit.
   *
   * @var int
   */
  public const MAX_LIMIT = 100000;

  /**
   * Retrieves all resources from the PokeAPI.
   *
   * @param string $endpoint
   *   The resource endpoint.
   * @param int $limit
   *   Maximum number of resources to retrieve. Default is the maximum limit.
   * @param int $offset
   *   The offset for pagination. Default is 0.
   *
   * @return array
   *   The resources.
   *
   * @throws \Drupal\pokemon_api\Exception\PokeApiException
   *   If an error occurs.
   */
  public function getResources(string $endpoint, int $limit = self::MAX_LIMIT, int $offset = 0): array;

  /**
   * Retrieves a resource from the PokeAPI.
   *
   * @param string $endpoint
   *   The resource endpoint.
   * @param int $id
   *   The resource id.
   *
   * @return \Drupal\pokemon_api\Resource\ResourceInterface
   *   The resource.
   *
   * @throws \Drupal\pokemon_api\Exception\PokeApiException
   *   If an error occurs.
   */
  public function getResource(string $endpoint, int $id): ResourceInterface;

}
