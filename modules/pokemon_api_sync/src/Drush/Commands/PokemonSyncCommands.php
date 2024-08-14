<?php

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api_sync\Sync\PokemonSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sync Pokemon Pokemon taxonomy.
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
   * Command to synchronize pokemon pokemons.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-pokemon', aliases: ['sync-pokemon'])]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-pokemon', description: 'Usage description')]
  public function syncPokemon(): void {

    $connection = $this->database->startTransaction();
    try {

      $this->pokemonSync->syncAll();
      if ($this->logger) {
        $this->logger->info('Pokemon pokemons synchronization successfully');
      }
    }
    catch (\Exception $e) {

      $connection->rollBack();
      if ($this->logger) {
        $this->logger->error($this->t('Failed to synchronize pokemon pokemons: @message', ['@message' => $e->getMessage()]));
        $this->logger->error($this->t('Stack trace: @stack_trace', [
          '@stack_trace' => $e->getTraceAsString(),
        ]));
      }

    }
  }

}
