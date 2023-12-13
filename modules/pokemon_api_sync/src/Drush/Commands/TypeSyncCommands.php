<?php

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\pokemon_api_sync\Sync\TypeSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sync Pokemon Type taxonomy.
 */
final class TypeSyncCommands extends DrushCommands {

  /**
   * Constructs a TypeSyncCommands object.
   */
  public function __construct(
    private readonly Connection $database,
    private readonly TypeSync $typeSync
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('database'),
      $container->get('pokemon_api_sync.type_sync'),
    );
  }

  /**
   * Command to synchronize pokemon types.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-type', aliases: ['sync-type'])]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-type', description: 'Usage description')]
  public function syncType(): void {

    $connection = $this->database->startTransaction();
    try {

      $this->typeSync->syncAll();
      if ($this->logger) {
        $this->logger->info('Pokemon types synchronization successfully');
      }
    }
    catch (\Exception $e) {

      $connection->rollBack();
      if ($this->logger) {
        $this->logger->error('Failed to synchronize pokemon types: @message', ['@message' => $e->getMessage()]);
      }

    }
  }

}
