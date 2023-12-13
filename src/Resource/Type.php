<?php

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Translation;

/**
 * Resource Type class.
 */
class Type extends Resource {

  /**
   * The moves.
   *
   * @var array|null
   */
  private array|null $moves;

  /**
   * The names.
   *
   * @var \Drupal\pokemon_api\Translation|null
   */
  private Translation|null $names;

  /**
   * The pokemon.
   *
   * @var array|null
   */
  private array|null $pokemon;

  /**
   * The endpoint.
   *
   * @var string
   */
  private const ENDPOINT = 'type';

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
  public function setMoves($moves) {
    $this->moves = $moves;
  }

  /**
   * Get the names.
   *
   * @return \Drupal\pokemon_api\Translation|null
   *   The names.
   */
  public function getNames(): ?Translation {
    return $this->names;
  }

  /**
   * Set the names.
   *
   * @param array $names
   *   The names.
   */
  public function setNames(array $names) {
    $this->names = new Translation($names);
  }

  /**
   * Get the pokemon.
   *
   * @return array
   *   The pokemon.
   */
  public function getPokemon(): array {
    return $this->pokemon;
  }

  /**
   * Set the pokemon.
   *
   * @param array $pokemon
   *   The pokemon.
   */
  public function setPokemon(array $pokemon) {
    $this->pokemon = $pokemon;
  }

}
