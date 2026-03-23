<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Endpoints;

/**
 * Evolution chain resource.
 */
class EvolutionChain extends TranslatableResource {

  /**
   * The parsed evolution chain as species IDs.
   *
   * @var int[]
   */
  private array $evolutions = [];

  /**
   * {@inheritdoc}
   */
  public static function getEndpoint(): string {
    return Endpoints::EVOLUTION_CHAIN->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromArray(array $data): static {
    $evolutionChain = parent::createFromArray($data);
    $evolutionChain->setEvolution($data['chain'] ?? []);

    return $evolutionChain;
  }

  /**
   * Gets the evolution chain species IDs.
   *
   * @return int[]
   *   The species IDs in evolution order.
   */
  public function getEvolution(): array {
    return $this->evolutions;
  }

  /**
   * Sets the evolution chain from raw API chain data.
   *
   * @param array $chain
   *   The raw chain data from the API.
   */
  public function setEvolution(array $chain): void {
    $evolutionChain = [];

    while (isset($chain['species'])) {
      $evolutionChain[] = self::extractIdFromUrl($chain['species']['url']);

      if (isset($chain['evolves_to'][0])) {
        $chain = $chain['evolves_to'][0];
      }
      else {
        break;
      }
    }

    $this->evolutions = $evolutionChain;
  }

}
