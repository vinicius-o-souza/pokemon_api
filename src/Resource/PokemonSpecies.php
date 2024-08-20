<?php

namespace Drupal\pokemon_api\Resource;

/**
 * Resource PokemonSpecies class.
 */
class PokemonSpecies extends Resource {

  /**
   * The endpoint.
   *
   * @var string
   */
  private const ENDPOINT = 'pokemon-species';

  /**
   * The is legendary flag.
   *
   * @var bool
   */
  private bool $isLegendary = FALSE;

  /**
   * The is mythical flag.
   *
   * @var bool
   */
  private bool $isMythical = FALSE;

  /**
   * The generation.
   *
   * @var \Drupal\pokemon_api\Resource\Generation
   */
  private Generation $generation;

  /**
   * Get the endpoint.
   *
   * @return string
   *   The endpoint.
   */
  public static function getEndpoint(): string {
    return self::ENDPOINT;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromArray(array $data): PokemonSpecies {
    $pokemonSpecies = new PokemonSpecies($data['name'], $data['url'] ?? NULL, $data['id'] ?? NULL);
    $pokemonSpecies->setIsLegendary($data['is_legendary'] ?? FALSE);
    $pokemonSpecies->setIsMythical($data['is_mythical'] ?? FALSE);
    $pokemonSpecies->setGeneration(Generation::createFromArray($data['generation'] ?? []));

    return $pokemonSpecies;
  }

  /**
   * Get the is legendary flag.
   *
   * @return bool
   *   The is legendary flag.
   */
  public function getIsLegendary(): bool {
    return $this->isLegendary;
  }

  /**
   * Set the is legendary flag.
   *
   * @param bool $isLegendary
   *   The is legendary flag.
   */
  public function setIsLegendary(bool $isLegendary): void {
    $this->isLegendary = $isLegendary;
  }

  /**
   * Get the is mythical flag.
   *
   * @return bool
   *   The is mythical flag.
   */
  public function getIsMythical(): bool {
    return $this->isMythical;
  }

  /**
   * Set the is mythical flag.
   *
   * @param bool $isMythical
   *   The is mythical flag.
   */
  public function setIsMythical(bool $isMythical): void {
    $this->isMythical = $isMythical;
  }

  /**
   * Get the generation.
   *
   * @return \Drupal\pokemon_api\Resource\Generation
   *   The generation.
   */
  public function getGeneration(): Generation {
    return $this->generation;
  }

  /**
   * Set the generation.
   *
   * @param \Drupal\pokemon_api\Resource\Generation $generation
   *   The generation.
   */
  public function setGeneration(Generation $generation): void {
    $this->generation = $generation;
  }

}
