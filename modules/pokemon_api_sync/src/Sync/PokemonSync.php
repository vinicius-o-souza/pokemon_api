<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\pokemon_api\ApiResource\PokemonApi;
use Drupal\pokemon_api\Resource\Resource;
use Drupal\pokemon_api\Resource\Pokemon;
use Drupal\pokemon_api_sync\SyncInterface;
use Drupal\pokemon_api_sync\SyncNodeEntity;

/**
 * Sync Pokemon node.
 */
class PokemonSync extends SyncNodeEntity implements SyncInterface {

  /**
   * Constructs a PokemonSync object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerChannelInterface $logger,
    private readonly PokemonApi $pokemonApi
  ) {}

  /**
   * {@inheritdoc}
   */
  public function syncAll(): void {
    $pokemons = $this->pokemonApi->getAllResources();

    foreach ($pokemons as $pokemon) {
      $this->sync($pokemon);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function sync(Resource $pokemon): void {
    $pokemon = $this->pokemonApi->getResource($pokemon->getId());

    $term = $this->readEntity($pokemon->getId());
    $data = $this->getDataFields($pokemon);

    if ($term) {
      $term = $this->updateEntity($term, $data);
    }
    else {
      $term = $this->createEntity($data);
    }
  }

  /**
   * {@inheritdoc}
   */
  private function getDataFields(Pokemon $pokemon): array {
    return [
      'name' => strtoupper($pokemon->getName()),
      'field_pokeapi_id' => $pokemon->getId(),
    ];
  }

}
