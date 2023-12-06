<?php

namespace Drupal\pokemon_api\ApiResource;

use Drupal\pokemon_api\ApiResourceInterface;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api\Resource\Resource;
use Drupal\pokemon_api\Resource\Type;

/**
 * Class TypeApi
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
   * @inheritdoc
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
   * @inheritdoc
   */
  public function getResource(int $id): Type {
    $response = $this->pokeApi->getResource(Type::getEndpoint(), $id);

    dd($response);

    $type = new Type($response['name'], $response['url']);
    $type->setDamageRelations($response['damage_relations']);
    $type->setGameIndices($response['game_indices']);
    $type->setGeneration($response['generation']);
    $type->setMoveDamageClass($response['move_damage_class']);
    $type->setMoves($response['moves']);
    $type->setNames($response['names']);
    $type->setPastDamageRelations($response['past_damage_relations']);
    $type->setPokemon($response['pokemon']);

    return $type;
  }

}