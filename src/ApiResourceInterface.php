<?php

namespace Drupal\pokemon_api;

use Drupal\pokemon_api\Resource\Resource;

/**
 * Interface for ApiResourceInterface.
 */
interface ApiResourceInterface {

  /**
   * Retrieves all resources from the PokeAPI.
   *
   * @return \Drupal\pokemon_api\Resource\Resource[]
   *   The resources.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getAllResources(): array;

  /**
   * Retrieves all resources from the PokeAPI.
   * 
   * @param int $limit
   *   The limit.
   * @param int $offset
   *   The offset.
   *
   * @return \Drupal\pokemon_api\Resource\Resource[]
   *   The resources.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getResourcesPagination(int $limit, int $offset): array;

  /**
   * Retrieves a resource from the PokeAPI.
   *
   * @param int $id
   *   The id of the resource.
   *
   * @return \Drupal\pokemon_api\Resource\Resource
   *   The resources.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getResource(int $id): Resource;

}
