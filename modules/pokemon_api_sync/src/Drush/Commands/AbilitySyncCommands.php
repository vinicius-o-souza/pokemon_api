<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api\PokeApiInterface;
use Drupal\pokemon_api_sync\Sync\AbilitySync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush commands for syncing Pokémon abilities.
 */
final class AbilitySyncCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs an AbilitySyncCommands object.
   */
  public function __construct(
    private readonly Connection $database,
    private readonly AbilitySync $abilitySync,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('database'),
      $container->get('pokemon_api_sync.ability_sync'),
    );
  }

  /**
   * Synchronizes Pokémon abilities from PokeAPI.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-ability', aliases: ['sync-ability'])]
  #[CLI\Option(name: 'limit', description: 'Limit of abilities to sync.')]
  #[CLI\Option(name: 'offset', description: 'Offset of abilities to sync.')]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-ability', description: 'Sync all Pokémon abilities.')]
  public function syncAbility(array $options = ['limit' => PokeApiInterface::MAX_LIMIT, 'offset' => 0]): void {
    $transaction = $this->database->startTransaction();
    $this->logger()->log('notice', 'Synchronizing Pokémon abilities...');

    try {
      $this->abilitySync->sync($options['limit'], $options['offset']);
      $this->logger()->log('success', 'Pokémon abilities synchronization completed successfully.');
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      $this->logger()->log('error', $this->t('Failed to synchronize Pokémon abilities: @message', ['@message' => $e->getMessage()]));
      $this->logger()->log('error', $this->t('Stack trace: @trace', ['@trace' => $e->getTraceAsString()]));
    }
  }

}
