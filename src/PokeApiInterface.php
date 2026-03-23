<?php

declare(strict_types=1);

namespace Drupal\pokemon_api;

use Drupal\pokemon_api\Resource\ResourceInterface;

/**
 * Interface for the PokeAPI client.
 */
interface PokeApiInterface {

  /**
   * The maximum resource limit.
   */
  public const int MAX_LIMIT = 100000;

  /**
   * Retrieves all resources from a PokeAPI endpoint.
   *
   * @param string $endpoint
   *   The resource endpoint.
   * @param int $limit
   *   Maximum number of resources to retrieve.
   * @param int $offset
   *   The offset for pagination.
   *
   * @return \Drupal\pokemon_api\Resource\ResourceInterface[]
   *   The resources.
   *
   * @throws \Drupal\pokemon_api\Exception\PokeApiException
   *   If an error occurs.
   */
  public function getResources(string $endpoint, int $limit = self::MAX_LIMIT, int $offset = 0): array;

  /**
   * Retrieves a single resource from the PokeAPI.
   *
   * @param string $endpoint
   *   The resource endpoint.
   * @param int $id
   *   The resource ID.
   *
   * @return \Drupal\pokemon_api\Resource\ResourceInterface
   *   The resource.
   *
   * @throws \Drupal\pokemon_api\Exception\PokeApiException
   *   If an error occurs.
   */
  public function getResource(string $endpoint, int $id): ResourceInterface;

}
