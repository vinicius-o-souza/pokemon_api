<?php

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api_sync\Sync\PokemonSpeciesSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sync Pokemon content type.
 */
final class PokemonSpeciesSyncCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs a PokemonSyncCommands object.
   */
  public function __construct(
    private readonly Connection $database,
    private readonly PokemonSpeciesSync $pokemonSpeciesSync,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('database'),
      $container->get('pokemon_api_sync.pokemon_species_sync'),
    );
  }

  /**
   * Command to synchronize pokemon.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-pokemon-species', aliases: ['sync-pokemon-species'])]
  #[CLI\Option(name: 'limit', description: 'Limit of pokemons species to sync.')]
  #[CLI\Option(name: 'offset', description: 'Offset of pokemons species to sync.')]
  public function syncPokemon(array $options = ['limit' => PokeApi::MAX_LIMIT, 'offset' => 0]): void {
    $connection = $this->database->startTransaction();
    $this->logger()->log('notice', 'Synchronizing pokemons species...');

    try {
      $this->pokemonSpeciesSync->sync($options['limit'], $options['offset']);
      if ($this->logger) {
        $this->logger()->log('success', 'Pokemons species synchronization successfully');
      }
    }
    catch (\Exception $e) {
      $connection->rollBack();
      if ($this->logger) {
        $this->logger()->log('error', $this->t('Failed to synchronize pokemon species: @message', ['@message' => $e->getMessage()]));
        $this->logger()->log('error', $this->t('Stack trace: @stack_trace', [
          '@stack_trace' => $e->getTraceAsString(),
        ]));
      }

    }
  }

}
