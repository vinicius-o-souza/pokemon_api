<?php

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api_sync\Sync\PokemonSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sync Pokemon content type.
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
   * Command to synchronize pokemon.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-pokemon', aliases: ['sync-pokemon'])]
  #[CLI\Option(name: 'limit', description: 'Limit of pokemons to sync.')]
  #[CLI\Option(name: 'offset', description: 'Offset of pokemons to sync.')]
  public function syncPokemon(array $options = ['limit' => PokeApi::MAX_LIMIT, 'offset' => 0]): void {
    $connection = $this->database->startTransaction();
    $this->logger()->log('notice', 'Synchronizing pokemons...');

    try {
      $this->pokemonSync->sync($options['limit'], $options['offset']);
      $this->logger()->log('success', 'Pokemons synchronization successfully');
    }
    catch (\Exception $e) {
      $connection->rollBack();
      $this->logger()->log('error', $this->t('Failed to synchronize pokemon: @message', ['@message' => $e->getMessage()]));
      $this->logger()->log('error', $this->t('Stack trace: @stack_trace', [
        '@stack_trace' => $e->getTraceAsString(),
      ]));

    }
  }

}
