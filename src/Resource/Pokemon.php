<?php

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Endpoints;

/**
 * Resource Pokemon class.
 */
class Pokemon extends Resource {

  /**
   * Order maximum.
   *
   * @var int
   */
  private const ORDER_MAXIMUM = 10000;

  /**
   * The base experience gained for defeating this Pokémon.
   *
   * @var int
   */
  private int $baseExperience = 0;

  /**
   * The height of this Pokémon in decimetres.
   *
   * @var float
   */
  private float $height = 0.0;

  /**
   * Order for sorting. Almost national order.
   *
   * @var int
   */
  private int $order;

  /**
   * The weight of this Pokémon in hectograms.
   *
   * @var float
   */
  private float $weight = 0.0;

  /**
   * A list of abilities this Pokémon could potentially have.
   *
   * @var array
   */
  private array $abilities;

  /**
   * A list of moves along with learn methods and level details pertaining.
   *
   * @var array
   */
  private array $moves;

  /**
   * A list of base stat values for this Pokémon.
   *
   * @var array
   */
  private array $stats;

  /**
   * A list of details showing types this Pokémon has.
   *
   * @var array
   */
  private array $types;

  /**
   * Get the endpoint.
   *
   * @return string
   *   The endpoint.
   */
  public static function getEndpoint(): string {
    return Endpoints::POKEMON->value;
  }

  /**
   * Get the base experience gained for defeating this Pokemon.
   *
   * @return int
   *   The base experience gained for defeating this Pokemon.
   */
  public function getBaseExperience(): int {
    return $this->baseExperience;
  }

  /**
   * Set the base experience gained for defeating this Pokemon.
   *
   * @param int $baseExperience
   *   The base experience gained for defeating this Pokemon.
   */
  public function setBaseExperience(int $baseExperience): void {
    $this->baseExperience = $baseExperience;
  }

  /**
   * Get the height of this Pokemon in decimetres.
   *
   * @return float
   *   The height of this Pokemon in decimetres.
   */
  public function getHeight(): float {
    return $this->height;
  }

  /**
   * Set the height of this Pokemon in decimetres.
   *
   * @param float $height
   *   The height of this Pokemon in decimetres.
   */
  public function setHeight(float $height): void {
    $this->height = $height / 10;
  }

  /**
   * Get the order for sorting. Almost national order.
   *
   * @return int
   *   The order for sorting. Almost national order.
   */
  public function getOrder(): int {
    return $this->order;
  }

  /**
   * Set the order for sorting. Almost national order.
   *
   * @param int $order
   *   The order for sorting. Almost national order.
   */
  public function setOrder(int $order): void {
    $this->order = $order < 0 ? self::ORDER_MAXIMUM + $order : $order;
  }

  /**
   * Get the weight of this Pokemon in hectograms.
   *
   * @return float
   *   The weight of this Pokemon in hectograms.
   */
  public function getWeight(): float {
    return $this->weight;
  }

  /**
   * Set the weight of this Pokemon in hectograms.
   *
   * @param float $weight
   *   The weight of this Pokemon in hectograms.
   */
  public function setWeight(float $weight): void {
    $this->weight = $weight / 10;
  }

  /**
   * Get the abilities.
   *
   * @return array
   *   The abilities.
   */
  public function getAbilities(): array {
    return $this->abilities;
  }

  /**
   * Set the abilities.
   *
   * @param array $abilities
   *   The abilities.
   */
  public function setAbilities(array $abilities): void {
    $pokemonAbilities = [];
    foreach ($abilities as $ability) {
      $pokemonAbilities[self::extractIdFromUrl($ability['ability']['url'])] = $ability['ability']['name'];
    }
    $this->abilities = $pokemonAbilities;
  }

  /**
   * Get the moves.
   *
   * @return array
   *   The moves.
   */
  public function getMoves(): array {
    return $this->moves;
  }

  /**
   * Set the moves.
   *
   * @param array $moves
   *   The moves.
   */
  public function setMoves(array $moves): void {
    $pokemonMoves = [];
    foreach ($moves as $move) {
      $pokemonMoves[self::extractIdFromUrl($move['move']['url'])] = $move['move']['name'];
    }
    $this->moves = $pokemonMoves;
  }

  /**
   * Get the stats.
   *
   * @return array
   *   The stats.
   */
  public function getStats(): array {
    return $this->stats;
  }

  /**
   * Set the stats.
   *
   * @param array $stats
   *   The stats.
   */
  public function setStats(array $stats): void {
    $pokemonStats = [];
    foreach ($stats as $stat) {
      $pokemonStats[self::extractIdFromUrl($stat['stat']['url'])] = $stat['base_stat'];
    }
    $this->stats = $pokemonStats;
  }

  /**
   * Get the types.
   *
   * @return array
   *   The types.
   */
  public function getTypes(): array {
    return $this->types;
  }

  /**
   * Set the types.
   *
   * @param array $types
   *   The types.
   */
  public function setTypes(array $types): void {
    $pokemonTypes = [];
    foreach ($types as $type) {
      $pokemonTypes[self::extractIdFromUrl($type['type']['url'])] = $type['type']['name'];
    }
    $this->types = $pokemonTypes;
  }

}
