<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api\PokeApiInterface;
use Drupal\pokemon_api_sync\Sync\TypeSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush commands for syncing Pokémon types.
 */
final class TypeSyncCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs a TypeSyncCommands object.
   */
  public function __construct(
    private readonly Connection $database,
    private readonly TypeSync $typeSync,
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
   * Synchronizes Pokémon types from PokeAPI.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-type', aliases: ['sync-type'])]
  #[CLI\Option(name: 'limit', description: 'Limit of types to sync.')]
  #[CLI\Option(name: 'offset', description: 'Offset of types to sync.')]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-type', description: 'Sync all Pokémon types.')]
  public function syncType(array $options = ['limit' => PokeApiInterface::MAX_LIMIT, 'offset' => 0]): void {
    $transaction = $this->database->startTransaction();
    $this->logger()->log('notice', 'Synchronizing Pokémon types...');

    try {
      $this->typeSync->sync($options['limit'], $options['offset']);
      $this->logger()->log('success', 'Pokémon types synchronization completed successfully.');
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      $this->logger()->log('error', $this->t('Failed to synchronize Pokémon types: @message', ['@message' => $e->getMessage()]));
      $this->logger()->log('error', $this->t('Stack trace: @trace', ['@trace' => $e->getTraceAsString()]));
    }
  }

}
