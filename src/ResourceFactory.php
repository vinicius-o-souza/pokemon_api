<?php

namespace Drupal\pokemon_api;

use Drupal\pokemon_api\Resource\Ability;
use Drupal\pokemon_api\Resource\EvolutionChain;
use Drupal\pokemon_api\Resource\Generation;
use Drupal\pokemon_api\Resource\Move;
use Drupal\pokemon_api\Resource\Pokemon;
use Drupal\pokemon_api\Resource\PokemonSpecies;
use Drupal\pokemon_api\Resource\Stat;
use Drupal\pokemon_api\Resource\Type;

/**
 * Resource factory.
 */
class ResourceFactory {

  /**
   * Get resource class name.
   *
   * @param string $endpoint
   *   The endpoint.
   *
   * @return string
   *   The resource class name.
   *
   * @throws \InvalidArgumentException
   *   If the endpoint is not valid.
   */
  public static function getResourceClass(string $endpoint): string {
    static $map = [
      Endpoints::ABILITY->value => Ability::class,
      Endpoints::EVOLUTION_CHAIN->value => EvolutionChain::class,
      Endpoints::GENERATION->value => Generation::class,
      Endpoints::MOVE->value => Move::class,
      Endpoints::POKEMON->value => Pokemon::class,
      Endpoints::POKEMON_SPECIES->value => PokemonSpecies::class,
      Endpoints::STAT->value => Stat::class,
      Endpoints::TYPE->value => Type::class,
    ];

    if (!isset($map[$endpoint])) {
      throw new \InvalidArgumentException(sprintf('The endpoint "%s" is not valid.', $endpoint));
    }

    return $map[$endpoint];
  }

}
