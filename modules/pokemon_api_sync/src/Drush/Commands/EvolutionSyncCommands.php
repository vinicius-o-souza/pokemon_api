<?php

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api_sync\Sync\EvolutionSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sync Pokemon Evolution.
 */
final class EvolutionSyncCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs a EvolutionSyncCommands object.
   */
  public function __construct(
    private readonly Connection $database,
    private readonly EvolutionSync $evolutionSync,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('database'),
      $container->get('pokemon_api_sync.evolution_sync'),
    );
  }

  /**
   * Command to synchronize pokemon evolution.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-evolution', aliases: ['sync-evolution'])]
  #[CLI\Option(name: 'limit', description: 'Limit of evolutions to sync.')]
  #[CLI\Option(name: 'offset', description: 'Offset of evolutions to sync.')]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-evolution', description: 'Command to synchronize pokemon evolutions.')]
  public function sync(array $options = ['limit' => PokeApi::MAX_LIMIT, 'offset' => 0]): void {
    $connection = $this->database->startTransaction();
    $this->logger()->log('notice', 'Synchronizing pokemon evolutions...');

    try {
      $this->evolutionSync->sync($options['limit'], $options['offset']);
      $this->logger()->log('success', 'Pokemon evolutions synchronization successfully');
    }
    catch (\Exception $e) {
      $connection->rollBack();
      $this->logger()->log('error', $this->t('Failed to synchronize pokemon evolutions: @message', ['@message' => $e->getMessage()]));
      $this->logger()->log('error', $this->t('Stack trace: @stack_trace', [
        '@stack_trace' => $e->getTraceAsString(),
      ]));

    }
  }

}
