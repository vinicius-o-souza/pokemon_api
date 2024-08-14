<?php

namespace Drupal\pokemon_api;

use Drupal\pokemon_api\Resource\ResourceInterface;

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
    return $this->pokeApi->getAllResources($this->getResourceModel());
  }

  /**
   * {@inheritdoc}
   */
  public function getResourcesPagination(int $limit, int $offset): ResponseResourceIterator {
    return $this->pokeApi->getResourcesPagination($this->getResourceModel(), $limit, $offset);
  }

  /**
   * {@inheritdoc}
   */
  public function getResource(int $id): ResourceInterface {
    return $this->pokeApi->getResource($this->getResourceModel(), $id);
  }

}
