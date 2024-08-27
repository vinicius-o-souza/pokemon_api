<?php

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Endpoints;

/**
 * Resource Move class.
 */
class Move extends TranslatableResource {

  use FlavorTextTrait;

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
  private int $effectChance;

  /**
   * Base power with a value of 0 if it does not have a base power.
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
   *
   * @var int
   */
  private int $priority;

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
    return Endpoints::MOVE->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromArray(array $data): static {
    $move = parent::createFromArray($data);
    $move->setAccuracy($data['accuracy'] ?? 0);
    $move->setEffectChance($data['effect_chance'] ?? 0);
    $move->setPower($data['power'] ?? 0);
    $move->setPowerPoints($data['pp'] ?? 0);
    $move->setPriority($data['priority'] ?? 0);
    $move->setFlavorText($data['flavor_text_entries'] ?? []);

    if (isset($data['type'])) {
      $type = Type::createFromArray($data['type']);
      $move->setType($type);
    }

    return $move;
  }

  /**
   * Gets the accuracy of this move.
   *
   * @return int
   *   The accuracy of this move.
   */
  public function getAccuracy(): int {
    return $this->accuracy;
  }

  /**
   * Sets the accuracy of this move.
   *
   * @param int $accuracy
   *   The accuracy of this move.
   */
  public function setAccuracy(int $accuracy): void {
    if ($accuracy < 0 || $accuracy > 100) {
      throw new \InvalidArgumentException('Accuracy must be between 0 and 100');
    }
    $this->accuracy = $accuracy;
  }

  /**
   * Gets the effect chance of this move.
   *
   * @return int
   *   The effect chance of this move.
   */
  public function getEffectChance(): int {
    return $this->effectChance;
  }

  /**
   * Sets the effect chance of this move.
   *
   * @param int $effectChance
   *   The effect chance of this move.
   */
  public function setEffectChance(int $effectChance): void {
    $this->effectChance = $effectChance;
  }

  /**
   * Gets the base power of this move.
   *
   * @return int
   *   The base power of this move.
   */
  public function getPower(): int {
    return $this->power;
  }

  /**
   * Sets the base power of this move.
   *
   * @param int $power
   *   The base power of this move.
   */
  public function setPower(int $power): void {
    $this->power = $power;
  }

  /**
   * Gets the power points of this move.
   *
   * @return int
   *   The power points of this move.
   */
  public function getPowerPoints(): int {
    return $this->powerPoints;
  }

  /**
   * Sets the power points of this move.
   *
   * @param int $powerPoints
   *   The power points of this move.
   */
  public function setPowerPoints(int $powerPoints): void {
    $this->powerPoints = $powerPoints;
  }

  /**
   * Gets the priority of this move.
   *
   * @return int
   *   The priority of this move.
   */
  public function getPriority(): int {
    return $this->priority;
  }

  /**
   * Sets the priority of this move.
   *
   * @param int $priority
   *   The priority of this move.
   */
  public function setPriority(int $priority): void {
    if ($priority < -8 || $priority > 8) {
      throw new \InvalidArgumentException('Priority must be between -8 and 8');
    }
    $this->priority = $priority;
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
   * @param \Drupal\pokemon_api\Resource\Type $type
   *   The type of this move.
   */
  public function setType(?Type $type = NULL): void {
    $this->type = $type;
  }

}
