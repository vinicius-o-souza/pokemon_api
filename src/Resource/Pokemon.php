<?php

namespace Drupal\pokemon_api\Resource;

/**
 * Resource Pokemon class.
 */
class Pokemon extends Resource {

  /**
   * The endpoint.
   *
   * @var string
   */
  private const ENDPOINT = 'pokemon';

  /**
   * The base experience gained for defeating this Pokémon.
   *
   * @var int
   */
  private int $baseExperience;

  /**
   * The height of this Pokémon in decimetres.
   *
   * @var int
   */
  private int $height;

  /**
   * Order for sorting. Almost national order.
   *
   * @var int
   */
  private int $order;

  /**
   * The weight of this Pokémon in hectograms.
   *
   * @var int
   */
  private int $weight;

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
   * A set of sprites used to depict this Pokémon in the game.
   *
   * @var array
   */
  private array $sprites;

  /**
   * The species this Pokémon belongs to.
   *
   * @var array
   */
  private array $species;

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
    return self::ENDPOINT;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromArray(array $data): Pokemon {
    $pokemon = new Pokemon($data['name'], $data['url'] ?? NULL, $data['id'] ?? NULL);
    $pokemon->setBaseExperience($data['base_experience'] ?? 0);
    $pokemon->setHeight($data['height'] ?? 0);
    $pokemon->setOrder($data['order'] ?? 0);
    $pokemon->setWeight($data['weight'] ?? 0);
    $pokemon->setAbilities($data['abilities'] ?? []);
    $pokemon->setMoves($data['moves'] ?? []);
    $pokemon->setSprites($data['sprites'] ?? []);
    $pokemon->setSpecies($data['species'] ?? []);
    $pokemon->setStats($data['stats'] ?? []);
    $pokemon->setTypes($data['types'] ?? []);

    return $pokemon;
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
   * @return int
   *   The height of this Pokemon in decimetres.
   */
  public function getHeight(): int {
    return $this->height;
  }

  /**
   * Set the height of this Pokemon in decimetres.
   *
   * @param int $height
   *   The height of this Pokemon in decimetres.
   */
  public function setHeight(int $height): void {
    $this->height = $height;
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
    $this->order = $order;
  }

  /**
   * Get the weight of this Pokemon in hectograms.
   *
   * @return int
   *   The weight of this Pokemon in hectograms.
   */
  public function getWeight(): int {
    return $this->weight;
  }

  /**
   * Set the weight of this Pokemon in hectograms.
   *
   * @param int $weight
   *   The weight of this Pokemon in hectograms.
   */
  public function setWeight(int $weight): void {
    $this->weight = $weight;
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
    $this->abilities = $abilities;
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
    $this->moves = $moves;
  }

  /**
   * Get the sprites.
   *
   * @return array
   *   The sprites.
   */
  public function getSprites(): array {
    return $this->sprites;
  }

  /**
   * Set the sprites.
   *
   * @param array $sprites
   *   The sprites.
   */
  public function setSprites(array $sprites): void {
    $this->sprites = $sprites;
  }

  /**
   * Get the species.
   *
   * @return array
   *   The species.
   */
  public function getSpecies(): array {
    return $this->species;
  }

  /**
   * Set the species.
   *
   * @param array $species
   *   The species.
   */
  public function setSpecies(array $species): void {
    $this->species = $species;
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
