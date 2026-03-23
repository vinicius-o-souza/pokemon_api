<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api\PokeApiInterface;
use Drupal\pokemon_api_sync\Sync\PokemonSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush commands for syncing Pokémon nodes.
 */
final class PokemonSyncCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs a PokemonSyncCommands object.
   */
  public function __construct(
    private readonly Connection $database,
    private readonly PokemonSync $pokemonSync,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('database'),
      $container->get('pokemon_api_sync.pokemon_sync'),
    );
  }

  /**
   * Synchronizes Pokémon data from PokeAPI.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-pokemon', aliases: ['sync-pokemon'])]
  #[CLI\Option(name: 'limit', description: 'Limit of Pokémon to sync.')]
  #[CLI\Option(name: 'offset', description: 'Offset of Pokémon to sync.')]
  public function syncPokemon(array $options = ['limit' => PokeApiInterface::MAX_LIMIT, 'offset' => 0]): void {
    $transaction = $this->database->startTransaction();
    $this->logger()->log('notice', 'Synchronizing Pokémon...');

    try {
      $this->pokemonSync->sync($options['limit'], $options['offset']);
      $this->logger()->log('success', 'Pokémon synchronization completed successfully.');
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      $this->logger()->log('error', $this->t('Failed to synchronize Pokémon: @message', ['@message' => $e->getMessage()]));
      $this->logger()->log('error', $this->t('Stack trace: @trace', ['@trace' => $e->getTraceAsString()]));
    }
  }

}
