<?php

namespace Drupal\pokemon_api\ApiResource;

use Drupal\pokemon_api\ApiResourceInterface;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api\ResponseResourceIterator;

/**
 * Base class for ApiResource.
 */
abstract class ApiResource implements ApiResourceInterface {

  /**
   * Constructs a new instance of the AbilityApi.
   *
   * @param \Drupal\pokemon_api\PokeApi $pokeApi
   *   The PokeApi instance.
   */
  public function __construct(
    protected readonly PokeApi $pokeApi,
  ) {}

  /**
   * Gets the resource model.
   *
   * @return string
   *   The resource model.
   */
  abstract protected function getResourceModel(): string;

  /**
   * {@inheritdoc}
   */
  public function getAllResources(): ResponseResourceIterator {
    return $this->pokeApi->getAllResources($this->getResourceModel()::getEndpoint());
  }

  /**
   * {@inheritdoc}
   */
  public function getResourcesPagination(int $limit, int $offset): ResponseResourceIterator {
    return $this->pokeApi->getResourcesPagination($this->getResourceModel()::getEndpoint(), $limit, $offset);
  }

  /**
   * {@inheritdoc}
   */
  public function getResource(int $id): ResourceInterface {
    return $this->pokeApi->getResource($this->getResourceModel()::getEndpoint(), $id);
  }

}
