<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api\PokeApiInterface;
use Drupal\pokemon_api_sync\Sync\GenerationSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush commands for syncing Pokémon generations.
 */
final class GenerationSyncCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs a GenerationSyncCommands object.
   */
  public function __construct(
    private readonly Connection $database,
    private readonly GenerationSync $generationSync,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('database'),
      $container->get('pokemon_api_sync.generation_sync'),
    );
  }

  /**
   * Synchronizes Pokémon generations from PokeAPI.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-generation', aliases: ['sync-generation'])]
  #[CLI\Option(name: 'limit', description: 'Limit of generations to sync.')]
  #[CLI\Option(name: 'offset', description: 'Offset of generations to sync.')]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-generation', description: 'Sync all Pokémon generations.')]
  public function sync(array $options = ['limit' => PokeApiInterface::MAX_LIMIT, 'offset' => 0]): void {
    $transaction = $this->database->startTransaction();
    $this->logger()->log('notice', 'Synchronizing Pokémon generations...');

    try {
      $this->generationSync->sync($options['limit'], $options['offset']);
      $this->logger()->log('success', 'Pokémon generations synchronization completed successfully.');
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      $this->logger()->log('error', $this->t('Failed to synchronize Pokémon generations: @message', ['@message' => $e->getMessage()]));
      $this->logger()->log('error', $this->t('Stack trace: @trace', ['@trace' => $e->getTraceAsString()]));
    }
  }

}
