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
   * @param \Drupal\pokemon_api\Resource\ResourceInterface $resource
   *   The resource.
   *
   * @return \Drupal\pokemon_api\ResponseResourceIterator
   *   The resource iterator.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getAllResources(ResourceInterface $resource): ResponseResourceIterator;

  /**
   * Retrieves all resources from the PokeAPI.
   *
   * @param \Drupal\pokemon_api\Resource\ResourceInterface $resource
   *   The resource.
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
  public function getResourcesPagination(ResourceInterface $resource, int $limit, int $offset = 0): ResponseResourceIterator;

  /**
   * Retrieves a resource from the PokeAPI.
   *
   * @param \Drupal\pokemon_api\Resource\ResourceInterface $resource
   *   The resource.
   *
   * @return \Drupal\pokemon_api\Resource\ResourceInterface
   *   The resource.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getResource(ResourceInterface $resource): ResourceInterface;

}
