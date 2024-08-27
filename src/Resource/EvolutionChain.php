<?php

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Endpoints;

/**
 * Resource EvolutionChain class.
 */
class EvolutionChain extends TranslatableResource {

  /**
   * The evolution chain.
   *
   * @var array
   */
  private array $evolutions = [];

  /**
   * Get the endpoint.
   *
   * @return string
   *   The endpoint.
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
   * Get the evolution chain.
   *
   * @return array
   *   The evolution chain.
   */
  public function getEvolution(): array {
    return $this->evolutions;
  }

  /**
   * Set the evolution chain.
   *
   * @param array $chain
   *   The evolution chain.
   */
  public function setEvolution(array $chain): void {
    $evolutionChain = [];

    while (isset($chain['species'])) {
      $specieUrl = $chain['species']['url'];
      $specieId = self::extractIdFromUrl($specieUrl);
      $evolutionChain[] = $specieId;

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
