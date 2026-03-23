<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\pokemon_api\Endpoints;
use Drupal\pokemon_api\PokeApiInterface;
use Drupal\pokemon_api\Resource\EvolutionChain;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api_sync\SyncNodeEntity;

/**
 * Syncs Pokémon evolution chains to node fields.
 */
class EvolutionSync extends SyncNodeEntity {

  /**
   * {@inheritdoc}
   */
  public function getContentType(): string {
    return 'pokemon';
  }

  /**
   * {@inheritdoc}
   */
  public function sync(int $limit = PokeApiInterface::MAX_LIMIT, int $offset = 0): void {
    $evolutions = $this->pokeApi->getResources(Endpoints::EVOLUTION_CHAIN->value, $limit, $offset);

    foreach ($evolutions as $evolution) {
      $evolution = $this->pokeApi->getResource($evolution->getEndpoint(), $evolution->getId());
      foreach ($evolution->getEvolution() as $pokemonId) {
        $pokemonNode = $this->readEntityByPokeId($pokemonId);
        $this->syncNode($evolution, $pokemonNode);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDataFields(ResourceInterface $resource, ?ContentEntityBase $node): array {
    if (!$resource instanceof EvolutionChain) {
      throw new \InvalidArgumentException('Expected EvolutionChain resource.');
    }

    $evolutions = [];
    foreach ($resource->getEvolution() as $pokemonId) {
      $pokemon = $this->readEntityByPokeId($pokemonId);
      if ($pokemon) {
        $evolutions[] = $pokemon->id();
      }
    }

    return [
      'field_pokemon_evolutions' => $evolutions,
    ];
  }

}
