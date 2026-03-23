<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api\PokeApiInterface;
use Drupal\pokemon_api_sync\Sync\PokemonSpeciesSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush commands for syncing Pokémon species data.
 */
final class PokemonSpeciesSyncCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs a PokemonSpeciesSyncCommands object.
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
   * Synchronizes Pokémon species data from PokeAPI.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-pokemon-species', aliases: ['sync-pokemon-species'])]
  #[CLI\Option(name: 'limit', description: 'Limit of Pokémon species to sync.')]
  #[CLI\Option(name: 'offset', description: 'Offset of Pokémon species to sync.')]
  public function syncPokemonSpecies(array $options = ['limit' => PokeApiInterface::MAX_LIMIT, 'offset' => 0]): void {
    $transaction = $this->database->startTransaction();
    $this->logger()->log('notice', 'Synchronizing Pokémon species...');

    try {
      $this->pokemonSpeciesSync->sync($options['limit'], $options['offset']);
      $this->logger()->log('success', 'Pokémon species synchronization completed successfully.');
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      $this->logger()->log('error', $this->t('Failed to synchronize Pokémon species: @message', ['@message' => $e->getMessage()]));
      $this->logger()->log('error', $this->t('Stack trace: @trace', ['@trace' => $e->getTraceAsString()]));
    }
  }

}
