<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api\PokeApiInterface;
use Drupal\pokemon_api_sync\Sync\StatSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush commands for syncing Pokémon stats.
 */
final class StatSyncCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs a StatSyncCommands object.
   */
  public function __construct(
    private readonly Connection $database,
    private readonly StatSync $statSync,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('database'),
      $container->get('pokemon_api_sync.stat_sync'),
    );
  }

  /**
   * Synchronizes Pokémon stats from PokeAPI.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-stat', aliases: ['sync-stat'])]
  #[CLI\Option(name: 'limit', description: 'Limit of stats to sync.')]
  #[CLI\Option(name: 'offset', description: 'Offset of stats to sync.')]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-stat', description: 'Sync all Pokémon stats.')]
  public function syncStat(array $options = ['limit' => PokeApiInterface::MAX_LIMIT, 'offset' => 0]): void {
    $transaction = $this->database->startTransaction();
    $this->logger()->log('notice', 'Synchronizing Pokémon stats...');

    try {
      $this->statSync->sync($options['limit'], $options['offset']);
      $this->logger()->log('success', 'Pokémon stats synchronization completed successfully.');
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      $this->logger()->log('error', $this->t('Failed to synchronize Pokémon stats: @message', ['@message' => $e->getMessage()]));
      $this->logger()->log('error', $this->t('Stack trace: @trace', ['@trace' => $e->getTraceAsString()]));
    }
  }

}
