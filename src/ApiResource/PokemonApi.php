<?php

namespace Drupal\pokemon_api\ApiResource;

use Drupal\pokemon_api\ApiResourceInterface;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api\Resource\Pokemon;

/**
 * Class PokemonApi to manage Pokemons.
 */
class PokemonApi implements ApiResourceInterface {

  /**
   * Constructs a new instance of the PokemonApi.
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
    $response = $this->pokeApi->getAllResources(Pokemon::getEndpoint());

    $pokemons = [];
    foreach ($response as $resource) {
      $pokemon = new Pokemon($resource['name'], $resource['url']);
      $pokemons[] = $pokemon;
    }

    return $pokemons;
  }

  /**
   * {@inheritdoc}
   */
  public function getResourcesPagination(int $limit, int $offset): array {
    $response = $this->pokeApi->getResourcesPagination(Pokemon::getEndpoint(), $limit, $offset);

    $pokemons = [];
    foreach ($response['results'] as $resource) {
      $pokemon = new Pokemon($resource['name'], $resource['url']);
      $pokemons[] = $pokemon;
    }

    return $pokemons;
  }

  /**
   * {@inheritdoc}
   */
  public function getResource(int $id): Pokemon {
    $response = $this->pokeApi->getResource(Pokemon::getEndpoint(), $id);

    $pokemon = new Pokemon($response['name'], NULL, $response['id']);
    $pokemon->setBaseExperience($response['base_experience'] ?? 0);
    $pokemon->setHeight($response['height'] ?? 0);
    $pokemon->setOrder($response['order'] ?? 0);
    $pokemon->setWeight($response['weight'] ?? 0);
    $pokemon->setAbilities($response['abilities']);
    $pokemon->setMoves($response['moves']);
    $pokemon->setSprites($response['sprites']);
    $pokemon->setSpecies($response['species']);
    $pokemon->setStats($response['stats']);
    $pokemon->setTypes($response['types']);

    return $pokemon;
  }

}
