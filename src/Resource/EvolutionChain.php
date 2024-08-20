<?php

namespace Drupal\pokemon_api\Resource;

/**
 * Resource EvolutionChain class.
 */
class EvolutionChain extends TranslatableResource {

  /**
   * The endpoint.
   *
   * @var string
   */
  private const ENDPOINT = 'evolution-chain';

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
    return self::ENDPOINT;
  }

  /**
   * {@inheritdoc}
   */
  public static function createFromArray(array $data): EvolutionChain {
    $evolutionChain = new EvolutionChain();
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

    while (isset($chain['evolves_to'])) {
      $specieUrl = $chain[0]['species']['url'];
      $specieId = self::extractIdFromUrl($specieUrl);
      $evolutionChain[] = $specieId;

      $chain = $chain['evolves_to'][0];
    }

    $this->evolutions = $evolutionChain;
  }

}
