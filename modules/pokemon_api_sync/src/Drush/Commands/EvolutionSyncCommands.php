<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api\PokeApiInterface;
use Drupal\pokemon_api_sync\Sync\EvolutionSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush commands for syncing Pokémon evolutions.
 */
final class EvolutionSyncCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs an EvolutionSyncCommands object.
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
   * Synchronizes Pokémon evolutions from PokeAPI.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-evolution', aliases: ['sync-evolution'])]
  #[CLI\Option(name: 'limit', description: 'Limit of evolutions to sync.')]
  #[CLI\Option(name: 'offset', description: 'Offset of evolutions to sync.')]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-evolution', description: 'Sync all Pokémon evolutions.')]
  public function sync(array $options = ['limit' => PokeApiInterface::MAX_LIMIT, 'offset' => 0]): void {
    $transaction = $this->database->startTransaction();
    $this->logger()->log('notice', 'Synchronizing Pokémon evolutions...');

    try {
      $this->evolutionSync->sync($options['limit'], $options['offset']);
      $this->logger()->log('success', 'Pokémon evolutions synchronization completed successfully.');
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      $this->logger()->log('error', $this->t('Failed to synchronize Pokémon evolutions: @message', ['@message' => $e->getMessage()]));
      $this->logger()->log('error', $this->t('Stack trace: @trace', ['@trace' => $e->getTraceAsString()]));
    }
  }

}
