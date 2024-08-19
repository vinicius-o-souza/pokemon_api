<?php

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Translation;

/**
 * Resource Move class.
 */
class Move extends TranslatableResource {

  /**
   * The endpoint.
   *
   * @var string
   */
  private const ENDPOINT = 'move';

  /**
   * The percent value of how likely this move is to be successful.
   *
   * @var int
   */
  private int $accuracy;

  /**
   * The percent value of how likely it is this moves effect will happen.
   *
   * @var int
   */
  private int $effectChange;

  /**
   * The base power of this move with a value of 0 if it does not have a base power.
   *
   * @var int
   */
  private int $power;

  /**
   * Power points. The number of times this move can be used.
   *
   * @var int
   */
  private int $powerPoints;

  /**
   * Sets the order in which moves are executed during battle.
   * A value between -8 and 8.
   *
   * @var int
   */
  private int $priority;

  /**
   * The flavor text of this move listed in different languages.
   *
   * @var \Drupal\pokemon_api\Translation
   */
  private Translation $flavorText;

  /**
   * The type of this move.
   *
   * @var \Drupal\pokemon_api\Resource\Type
   */
  private Type|null $type;

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
  public static function createFromArray(array $data): Move {
    $move = new Move($data['name'], $data['url'] ?? NULL, $data['id'] ?? NULL);
    $move->setNames($data['names'] ?? []);
    $move->setAccuracy($data['accuracy'] ?? 0);
    $move->setEffectChange($data['effect_chance'] ?? 0);
    $move->setPower($data['power'] ?? 0);
    $move->setPowerPoints($data['pp'] ?? 0);
    $move->setPriority($data['priority'] ?? 0);
    $move->setFlavorText($data['flavor_text_entries'] ?? []);

    if (isset($data['type'])) {
      $type = new Type($data['type']['name'], $data['type']['url'] ?? NULL, $data['type']['id'] ?? NULL);
      $move->setType($type); 
    }

    return $move;
  }

  /**
   * Gets the accuracy of this move.
   *
   * @return int
   */
  public function getAccuracy(): int {
    return $this->accuracy;
  }

  /**
   * Sets the accuracy of this move.
   *
   * @param int $accuracy
   */
  public function setAccuracy(int $accuracy): void {
    if ($accuracy < 0 || $accuracy > 100) {
      throw new \InvalidArgumentException('Accuracy must be between 0 and 100');
    }
    $this->accuracy = $accuracy;
  }

  /**
   * Gets the effect change of this move.
   *
   * @return string
   */
  public function getEffectChange(): string {
    return $this->effectChange;
  }

  /**
   * Sets the effect change of this move.
   *
   * @param string $effectChange
   */
  public function setEffectChange(string $effectChange): void {
    $this->effectChange = $effectChange;
  }

  /**
   * Gets the base power of this move.
   *
   * @return int
   */
  public function getPower(): int {
    return $this->power;
  }

  /**
   * Sets the base power of this move.
   *
   * @param int $power
   */
  public function setPower(int $power): void {
    $this->power = $power;
  }

  /**
   * Gets the power points of this move.
   *
   * @return int
   */
  public function getPowerPoints(): int {
    return $this->powerPoints;
  }

  /**
   * Sets the power points of this move.
   *
   * @param int $powerPoints
   */
  public function setPowerPoints(int $powerPoints): void {
    $this->powerPoints = $powerPoints;
  }

  /**
   * Gets the priority of this move.
   *
   * @return int
   */
  public function getPriority(): int {
    return $this->priority;
  }

  /**
   * Sets the priority of this move.
   *
   * @param int $priority
   */
  public function setPriority(int $priority): void {
    if ($priority < -8 || $priority > 8) {
      throw new \InvalidArgumentException('Priority must be between -8 and 8');
    }
    $this->priority = $priority;
  }

  /**
   * Get the effect of this move listed in different languages.
   *
   * @return \Drupal\pokemon_api\Translation
   *   The flavor text of this move listed in different languages.
   */
  public function getFlavorText(): Translation {
    return $this->flavorText;
  }

  /**
   * Set the effect of this move listed in different languages.
   *
   * @param array $flavorText
   *   The flavor text of this move listed in different languages.
   */
  public function setFlavorText(array $flavorText): void {
    $this->flavorText = new Translation($flavorText, 'flavor_text');
  }

  /**
   * Get the type of this move.
   *
   * @return \Drupal\pokemon_api\Resource\Type
   *   The type of this move.
   */
  public function getType(): Type {
    return $this->type;
  }

  /**
   * Set the type of this move.
   *
   * @param \Drupal\pokemon_api\Resource\Type
   *   The type of this move.
   */
  public function setType(?Type $type = NULL): void {
    $this->type = $type;
  }

}
