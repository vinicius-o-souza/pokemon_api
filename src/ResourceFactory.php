<?php

declare(strict_types=1);

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
 * Maps PokeAPI endpoints to their resource classes.
 */
class ResourceFactory {

  /**
   * Endpoint-to-resource class mapping.
   *
   * @var array<string, class-string<\Drupal\pokemon_api\Resource\Resource>>
   */
  private const array RESOURCE_MAP = [
    'ability' => Ability::class,
    'evolution-chain' => EvolutionChain::class,
    'generation' => Generation::class,
    'move' => Move::class,
    'pokemon' => Pokemon::class,
    'pokemon-species' => PokemonSpecies::class,
    'stat' => Stat::class,
    'type' => Type::class,
  ];

  /**
   * Gets the resource class for a given endpoint.
   *
   * @param string $endpoint
   *   The endpoint.
   *
   * @return class-string<\Drupal\pokemon_api\Resource\Resource>
   *   The resource class name.
   *
   * @throws \InvalidArgumentException
   *   If the endpoint has no mapped resource class.
   */
  public static function getResourceClass(string $endpoint): string {
    if (!isset(self::RESOURCE_MAP[$endpoint])) {
      throw new \InvalidArgumentException(sprintf('The endpoint "%s" is not valid.', $endpoint));
    }

    return self::RESOURCE_MAP[$endpoint];
  }

}
