<?php

namespace Drupal\pokemon_api\Resource;

/**
 * Class Type.
 */
class Type extends Resource {

  /**
   * The damage relations.
   *
   * @var array
   */
  private array $damageRelations;

  /**
   * The game indices.
   *
   * @var array
   */
  private array $gameIndices;

  /**
   * The generation.
   *
   * @var array
   */
  private array $generation;

  /**
   * The move damage class.
   *
   * @var array
   */
  private array $moveDamageClass;

  /**
   * The moves.
   *
   * @var array
   */
  private array $moves;

  /**
   * The names.
   *
   * @var array
   */
  private array $names;

  /**
   * The past damage relations.
   *
   * @var array
   */
  private array $pastDamageRelations;

  /**
   * The pokemon.
   *
   * @var array
   */
  private array $pokemon;

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
   * Get the damage relations.
   *
   * @return array
   *   The damage relations.
   */
  public function getDamageRelations(): array {
    return $this->damageRelations;
  }

  /**
   * Set the damage relations.
   *
   * @param array $damageRelations
   *   The damage relations.
   */
  public function setDamageRelations($damageRelations) {
    $this->damageRelations = $damageRelations;
  }

  /**
   * Get the game indices.
   *
   * @return array
   *   The game indices.
   */
  public function getGameIndices(): array {
    return $this->gameIndices;
  }

  /**
   * Set the game indices.
   *
   * @param array $gameIndices
   *   The game indices.
   */
  public function setGameIndices($gameIndices) {
    $this->gameIndices = $gameIndices;
  }

  /**
   * Get the generation.
   *
   * @return array
   *   The generation.
   */
  public function getGeneration(): array {
    return $this->generation;
  }

  /**
   * Set the generation.
   *
   * @param array $generation
   *   The generation.
   */
  public function setGeneration($generation) {
    $this->generation = $generation;
  }

  /**
   * Get the move damage class.
   *
   * @return array
   *   The move damage class.
   */
  public function getMoveDamageClass(): array {
    return $this->moveDamageClass;
  }

  /**
   * Set the move damage class.
   *
   * @param array $moveDamageClass
   *   The move damage class.
   */
  public function setMoveDamageClass($moveDamageClass) {
    $this->moveDamageClass = $moveDamageClass;
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
   * @return array
   *   The names.
   */
  public function getNames(): array {
    return $this->names;
  }

  /**
   * Set the names.
   *
   * @param array $names
   *   The names.
   */
  public function setNames(array $names) {
    $this->names = $names;
  }

  /**
   * Get the past damage relations.
   *
   * @return array
   *   The past damage relations.
   */
  public function getPastDamageRelations(): array {
    return $this->pastDamageRelations;
  }

  /**
   * Set the past damage relations.
   *
   * @param array $pastDamageRelations
   *   The past damage relations.
   */
  public function setPastDamageRelations(array $pastDamageRelations) {
    $this->pastDamageRelations = $pastDamageRelations;
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