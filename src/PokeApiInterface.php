<?php

namespace Drupal\pokemon_api;

use Drupal\pokemon_api\Resource\ResourceInterface;

/**
 * Interface for PokeApiInterface.
 */
interface PokeApiInterface {

  /**
   * Retrieves all resources from the PokeAPI.
   *
   * @param string $resourceClass
   *   The resource string class.
   *
   * @return \Drupal\pokemon_api\ResponseResourceIterator
   *   The resource iterator.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getAllResources(string $resourceClass): ResponseResourceIterator;

  /**
   * Retrieves all resources from the PokeAPI.
   *
   * @param string $resourceClass
   *   The resource string class.
   * @param int $limit
   *   Limit the number of items.
   * @param int $offset
   *   Offset the items.
   *
   * @return \Drupal\pokemon_api\ResponseResourceIterator
   *   The resource iterator.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getResourcesPagination(string $resourceClass, int $limit, int $offset): ResponseResourceIterator;

  /**
   * Retrieves a resource from the PokeAPI.
   *
   * @param string $resourceClass
   *   The resource string class.
   * @param int $id
   *   The id of the resource.
   *
   * @return \Drupal\pokemon_api\Resource\ResourceInterface
   *   The resource.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getResource(string $resourceClass, int $id): ResourceInterface;

}
