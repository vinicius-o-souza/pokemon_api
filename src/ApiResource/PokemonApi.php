<?php

namespace Drupal\pokemon_api\ApiResource;

use Drupal\pokemon_api\Resource\Pokemon;

/**
 * Class PokemonApi to manage Pokemons.
 */
class PokemonApi extends ApiResource {

  /**
   * {@inheritdoc}
   *
   * @return class-string<Pokemon>
   *   The resource model.
   */
  protected function getResourceModel(): string {
    return Pokemon::class;
  }

}
