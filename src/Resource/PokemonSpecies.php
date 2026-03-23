<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Endpoints;

/**
 * Pokemon species resource.
 */
class PokemonSpecies extends Resource {

  /**
   * Whether this Pokémon is legendary.
   */
  private bool $isLegendary = FALSE;

  /**
   * Whether this Pokémon is mythical.
   */
  private bool $isMythical = FALSE;

  /**
   * The generation this species belongs to.
   */
  private Generation $generation;

  /**
   * {@inheritdoc}
   */
  public static function getEndpoint(): string {
    return Endpoints::PokemonSpecies->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromArray(array $data): static {
    $pokemonSpecies = parent::createFromArray($data);
    $pokemonSpecies->setIsLegendary($data['is_legendary'] ?? FALSE);
    $pokemonSpecies->setIsMythical($data['is_mythical'] ?? FALSE);

    if (isset($data['generation'])) {
      $pokemonSpecies->setGeneration(Generation::createFromArray($data['generation']));
    }

    return $pokemonSpecies;
  }

  /**
   * Gets whether this Pokémon is legendary.
   */
  public function getIsLegendary(): bool {
    return $this->isLegendary;
  }

  /**
   * Sets the legendary flag.
   */
  public function setIsLegendary(bool $isLegendary): void {
    $this->isLegendary = $isLegendary;
  }

  /**
   * Gets whether this Pokémon is mythical.
   */
  public function getIsMythical(): bool {
    return $this->isMythical;
  }

  /**
   * Sets the mythical flag.
   */
  public function setIsMythical(bool $isMythical): void {
    $this->isMythical = $isMythical;
  }

  /**
   * Gets the generation.
   */
  public function getGeneration(): Generation {
    return $this->generation;
  }

  /**
   * Sets the generation.
   */
  public function setGeneration(Generation $generation): void {
    $this->generation = $generation;
  }

}
