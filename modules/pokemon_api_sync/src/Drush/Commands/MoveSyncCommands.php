<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api\PokeApiInterface;
use Drupal\pokemon_api_sync\Sync\MoveSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush commands for syncing Pokémon moves.
 */
final class MoveSyncCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs a MoveSyncCommands object.
   */
  public function __construct(
    private readonly Connection $database,
    private readonly MoveSync $moveSync,
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
   * Synchronizes Pokémon moves from PokeAPI.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-move', aliases: ['sync-move'])]
  #[CLI\Option(name: 'limit', description: 'Limit of moves to sync.')]
  #[CLI\Option(name: 'offset', description: 'Offset of moves to sync.')]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-move', description: 'Sync all Pokémon moves.')]
  public function syncMove(array $options = ['limit' => PokeApiInterface::MAX_LIMIT, 'offset' => 0]): void {
    $transaction = $this->database->startTransaction();
    $this->logger()->log('notice', 'Synchronizing Pokémon moves...');

    try {
      $this->moveSync->sync($options['limit'], $options['offset']);
      $this->logger()->log('success', 'Pokémon moves synchronization completed successfully.');
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      $this->logger()->log('error', $this->t('Failed to synchronize Pokémon moves: @message', ['@message' => $e->getMessage()]));
      $this->logger()->log('error', $this->t('Stack trace: @trace', ['@trace' => $e->getTraceAsString()]));
    }
  }

}
