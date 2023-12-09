<?php

namespace Drupal\pokemon_api;

/**
 * Interface for PokeApiInterface.
 */
interface PokeApiInterface {

  /**
   * Retrieves all resources from the PokeAPI.
   *
   * @param string $endpoint
   *   The endpoint string.
   *
   * @return array
   *   The response from the API.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getAllResources(string $endpoint): array;

  /**
   * Retrieves all resources from the PokeAPI.
   *
   * @param string $endpoint
   *   The endpoint string.
   * @param int $limit
   *   Limit the number of items.
   * @param int $offset
   *   Offset the items.
   *
   * @return array
   *   The response from the API.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getResourcesPagination(string $endpoint, int $limit, int $offset): array;

  /**
   * Retrieves a resource from the PokeAPI.
   *
   * @param string $endpoint
   *   The endpoint string.
   * @param int $id
   *   The id of the resource.
   *
   * @return array
   *   The response from the API.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getResource(string $endpoint, int $id): array;

}
