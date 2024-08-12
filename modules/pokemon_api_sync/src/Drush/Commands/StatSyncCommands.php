<?php

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api_sync\Sync\StatSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sync Pokemon Stat taxonomy.
 */
final class StatSyncCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs a StatSyncCommands object.
   */
  public function __construct(
    private readonly Connection $database,
    private readonly StatSync $statSync
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
   * Command to synchronize pokemon Stats.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-stat', aliases: ['sync-stat'])]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-stat', description: 'Usage description')]
  public function syncStat(): void {

    $connection = $this->database->startTransaction();
    try {

      $this->statSync->syncAll();
      if ($this->logger) {
        $this->logger->info('Pokemon Stats synchronization successfully');
      }
    }
    catch (\Exception $e) {

      $connection->rollBack();
      if ($this->logger) {
        $this->logger->error($this->t('Failed to synchronize pokemon stats: @message', ['@message' => $e->getMessage()]));
      }

    }
  }

}
