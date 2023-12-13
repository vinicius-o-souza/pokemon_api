<?php

namespace Drupal\pokemon_api\ApiResource;

use Drupal\pokemon_api\ApiResourceInterface;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api\Resource\Type;

/**
 * Class TypeApi to manage Types.
 */
class TypeApi implements ApiResourceInterface {

  /**
   * Constructs a new instance of the TypeApi.
   *
   * @param \Drupal\pokemon_api\PokeApi $pokeApi
   *   The PokeApi instance.
   */
  public function __construct(
    private readonly PokeApi $pokeApi
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getAllResources(): array {
    $response = $this->pokeApi->getAllResources(Type::getEndpoint());

    $types = [];
    foreach ($response as $resource) {
      $type = new Type($resource['name'], $resource['url']);
      $types[] = $type;
    }

    return $types;
  }

  /**
   * {@inheritdoc}
   */
  public function getResourcesPagination(int $limit, int $offset): array {
    $response = $this->pokeApi->getResourcesPagination(Type::getEndpoint(), $limit, $offset);

    $types = [];
    foreach ($response['results'] as $resource) {
      $type = new Type($resource['name'], $resource['url']);
      $types[] = $type;
    }

    return $types;
  }

  /**
   * {@inheritdoc}
   */
  public function getResource(int $id): Type {
    $response = $this->pokeApi->getResource(Type::getEndpoint(), $id);

    $type = new Type($response['name'], NULL, $response['id']);
    $type->setMoves($response['moves']);
    $type->setNames($response['names']);
    $type->setPokemon($response['pokemon']);

    return $type;
  }

}
