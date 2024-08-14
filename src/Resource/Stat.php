<?php

namespace Drupal\pokemon_api\Resource;

/**
 * Resource Stat class.
 */
class Stat extends TranslatableResource {

  /**
   * The endpoint.
   *
   * @var string
   */
  private const ENDPOINT = 'stat';

  /**
   * The base stat.
   *
   * @var int
   */
  private int $baseStat;

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
  public static function createFromArray(array $data): Stat {
    $stat = new Stat($data['name'], $data['url'] ?? NULL, $data['id'] ?? NULL);
    $stat->setNames($data['names'] ?? []);
    $stat->setBaseStat($data['base_stat'] ?? 0);

    return $stat;
  }

  /**
   * Get the base stat gained for defeating this Pokemon.
   *
   * @return int
   *   The base stat gained for defeating this Pokemon.
   */
  public function getBaseStat(): int {
    return $this->baseStat;
  }

  /**
   * Set the base stat gained for defeating this Pokemon.
   *
   * @param int $baseStat
   *   The base stat gained for defeating this Pokemon.
   */
  public function setBaseStat(int $baseStat): void {
    $this->baseStat = $baseStat;
  }

}
