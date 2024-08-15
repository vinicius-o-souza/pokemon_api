<?php

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api_sync\Sync\MoveSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sync Pokemon Move taxonomy.
 */
final class MoveSyncCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs a MoveSyncCommands object.
   */
  public function __construct(
    private readonly Connection $database,
    private readonly MoveSync $abilitySync,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('database'),
      $container->get('pokemon_api_sync.move_sync'),
    );
  }

  /**
   * Command to synchronize pokemon moves.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-move', aliases: ['sync-move'])]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-move', description: 'Usage description')]
  public function syncMove(): void {

    $connection = $this->database->startTransaction();
    try {

      $this->abilitySync->syncAll();
      if ($this->logger) {
        $this->logger->info('Pokemon moves synchronization successfully');
      }
    }
    catch (\Exception $e) {

      $connection->rollBack();
      if ($this->logger) {
        $this->logger->error($this->t('Failed to synchronize pokemon moves: @message', ['@message' => $e->getMessage()]));
        $this->logger->error($this->t('Stack trace: @stack_trace', [
          '@stack_trace' => $e->getTraceAsString(),
        ]));
      }

    }
  }

}
