<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Endpoints;

/**
 * Represents a Pokémon from the PokeAPI.
 */
class Pokemon extends Resource {

  /**
   * Maximum order value for negative-order Pokémon.
   */
  private const int ORDER_MAXIMUM = 10000;

  /**
   * The base experience gained for defeating this Pokémon.
   */
  private int $baseExperience = 0;

  /**
   * The height in decimetres (converted to metres).
   */
  private float $height = 0.0;

  /**
   * Sort order (almost national order).
   */
  private int $order;

  /**
   * The weight in hectograms (converted to kilograms).
   */
  private float $weight = 0.0;

  /**
   * Abilities keyed by PokeAPI ID.
   *
   * @var array<int, string>
   */
  private array $abilities;

  /**
   * Moves keyed by PokeAPI ID.
   *
   * @var array<int, string>
   */
  private array $moves;

  /**
   * Base stats keyed by PokeAPI ID.
   *
   * @var array<int, int>
   */
  private array $stats;

  /**
   * Types keyed by PokeAPI ID.
   *
   * @var array<int, string>
   */
  private array $types;

  /**
   * {@inheritdoc}
   */
  public static function getEndpoint(): string {
    return Endpoints::Pokemon->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromArray(array $data): static {
    $pokemon = parent::createFromArray($data);
    $pokemon->setBaseExperience($data['base_experience'] ?? 0);
    $pokemon->setHeight($data['height'] ?? 0.0);
    $pokemon->setOrder($data['order']);
    $pokemon->setWeight($data['weight'] ?? 0.0);
    $pokemon->setAbilities($data['abilities']);
    $pokemon->setMoves($data['moves']);
    $pokemon->setStats($data['stats']);
    $pokemon->setTypes($data['types']);

    return $pokemon;
  }

  /**
   * Gets the base experience.
   */
  public function getBaseExperience(): int {
    return $this->baseExperience;
  }

  /**
   * Sets the base experience.
   */
  public function setBaseExperience(int $baseExperience): void {
    $this->baseExperience = $baseExperience;
  }

  /**
   * Gets the height in metres.
   */
  public function getHeight(): float {
    return $this->height;
  }

  /**
   * Sets the height, converting from decimetres to metres.
   */
  public function setHeight(float $height): void {
    $this->height = $height / 10;
  }

  /**
   * Gets the sort order.
   */
  public function getOrder(): int {
    return $this->order;
  }

  /**
   * Sets the sort order, normalising negative values.
   */
  public function setOrder(int $order): void {
    $this->order = $order < 0 ? self::ORDER_MAXIMUM + $order : $order;
  }

  /**
   * Gets the weight in kilograms.
   */
  public function getWeight(): float {
    return $this->weight;
  }

  /**
   * Sets the weight, converting from hectograms to kilograms.
   */
  public function setWeight(float $weight): void {
    $this->weight = $weight / 10;
  }

  /**
   * Gets the abilities.
   *
   * @return array<int, string>
   *   Abilities keyed by PokeAPI ID.
   */
  public function getAbilities(): array {
    return $this->abilities;
  }

  /**
   * Sets the abilities from raw API data.
   */
  public function setAbilities(array $abilities): void {
    $this->abilities = [];
    foreach ($abilities as $ability) {
      $id = self::extractIdFromUrl($ability['ability']['url']);
      $this->abilities[$id] = $ability['ability']['name'];
    }
  }

  /**
   * Gets the moves.
   *
   * @return array<int, string>
   *   Moves keyed by PokeAPI ID.
   */
  public function getMoves(): array {
    return $this->moves;
  }

  /**
   * Sets the moves from raw API data.
   */
  public function setMoves(array $moves): void {
    $this->moves = [];
    foreach ($moves as $move) {
      $id = self::extractIdFromUrl($move['move']['url']);
      $this->moves[$id] = $move['move']['name'];
    }
  }

  /**
   * Gets the base stats.
   *
   * @return array<int, int>
   *   Stats keyed by PokeAPI ID.
   */
  public function getStats(): array {
    return $this->stats;
  }

  /**
   * Sets the stats from raw API data.
   */
  public function setStats(array $stats): void {
    $this->stats = [];
    foreach ($stats as $stat) {
      $id = self::extractIdFromUrl($stat['stat']['url']);
      $this->stats[$id] = $stat['base_stat'];
    }
  }

  /**
   * Gets the types.
   *
   * @return array<int, string>
   *   Types keyed by PokeAPI ID.
   */
  public function getTypes(): array {
    return $this->types;
  }

  /**
   * Sets the types from raw API data.
   */
  public function setTypes(array $types): void {
    $this->types = [];
    foreach ($types as $type) {
      $id = self::extractIdFromUrl($type['type']['url']);
      $this->types[$id] = $type['type']['name'];
    }
  }

}
