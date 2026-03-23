<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Endpoints;

/**
 * Represents a Pokémon move from the PokeAPI.
 */
class Move extends TranslatableResource {

  use FlavorTextTrait;

  /**
   * The accuracy percentage (0-100).
   */
  private int $accuracy;

  /**
   * The effect chance percentage.
   */
  private int $effectChance;

  /**
   * The base power (0 if none).
   */
  private int $power;

  /**
   * The number of times this move can be used.
   */
  private int $powerPoints;

  /**
   * The execution priority (-8 to 8).
   */
  private int $priority;

  /**
   * The type of this move.
   */
  private ?Type $type = NULL;

  /**
   * {@inheritdoc}
   */
  public static function getEndpoint(): string {
    return Endpoints::Move->value;
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
      $move->setType(Type::createFromArray($data['type']));
    }

    return $move;
  }

  /**
   * Gets the accuracy.
   */
  public function getAccuracy(): int {
    return $this->accuracy;
  }

  /**
   * Sets the accuracy.
   *
   * @param int $accuracy
   *   The accuracy percentage (0-100).
   *
   * @throws \InvalidArgumentException
   *   If accuracy is out of range.
   */
  public function setAccuracy(int $accuracy): void {
    if ($accuracy < 0 || $accuracy > 100) {
      throw new \InvalidArgumentException('Accuracy must be between 0 and 100.');
    }
    $this->accuracy = $accuracy;
  }

  /**
   * Gets the effect chance.
   */
  public function getEffectChance(): int {
    return $this->effectChance;
  }

  /**
   * Sets the effect chance.
   */
  public function setEffectChance(int $effectChance): void {
    $this->effectChance = $effectChance;
  }

  /**
   * Gets the base power.
   */
  public function getPower(): int {
    return $this->power;
  }

  /**
   * Sets the base power.
   */
  public function setPower(int $power): void {
    $this->power = $power;
  }

  /**
   * Gets the power points.
   */
  public function getPowerPoints(): int {
    return $this->powerPoints;
  }

  /**
   * Sets the power points.
   */
  public function setPowerPoints(int $powerPoints): void {
    $this->powerPoints = $powerPoints;
  }

  /**
   * Gets the priority.
   */
  public function getPriority(): int {
    return $this->priority;
  }

  /**
   * Sets the priority.
   *
   * @param int $priority
   *   The priority (-8 to 8).
   *
   * @throws \InvalidArgumentException
   *   If priority is out of range.
   */
  public function setPriority(int $priority): void {
    if ($priority < -8 || $priority > 8) {
      throw new \InvalidArgumentException('Priority must be between -8 and 8.');
    }
    $this->priority = $priority;
  }

  /**
   * Gets the move type.
   */
  public function getType(): ?Type {
    return $this->type;
  }

  /**
   * Sets the move type.
   */
  public function setType(?Type $type = NULL): void {
    $this->type = $type;
  }

}
