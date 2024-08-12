<?php

namespace Drupal\pokemon_api\ApiResource;

use Drupal\pokemon_api\ApiResourceInterface;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api\Resource\Stat;

/**
 * Class StatApi to manage Stats.
 */
class StatApi implements ApiResourceInterface {

  /**
   * Constructs a new instance of the StatApi.
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
    $response = $this->pokeApi->getAllResources(Stat::getEndpoint());

    $stats = [];
    foreach ($response as $resource) {
      $type = new Stat($resource['name'], $resource['url']);
      $stats[] = $type;
    }

    return $stats;
  }

  /**
   * {@inheritdoc}
   */
  public function getResourcesPagination(int $limit, int $offset): array {
    $response = $this->pokeApi->getResourcesPagination(Stat::getEndpoint(), $limit, $offset);

    $stats = [];
    foreach ($response as $resource) {
      $type = new Stat($resource['name'], $resource['url']);
      $stats[] = $type;
    }

    return $stats;
  }

  /**
   * {@inheritdoc}
   */
  public function getResource(int $id): Stat {
    $response = $this->pokeApi->getResource(Stat::getEndpoint(), $id);

    $type = new Stat($response['name'], NULL, $response['id']);
    $type->setNames($response['names']);

    return $type;
  }

}
