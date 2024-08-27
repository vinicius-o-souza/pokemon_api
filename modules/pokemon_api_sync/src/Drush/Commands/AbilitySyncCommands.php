<?php

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api_sync\Sync\AbilitySync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sync Pokemon Ability taxonomy.
 */
final class AbilitySyncCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Constructs a AbilitySyncCommands object.
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
   * Command to synchronize pokemon abilities.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-ability', aliases: ['sync-ability'])]
  #[CLI\Option(name: 'limit', description: 'Limit of abilities to sync.')]
  #[CLI\Option(name: 'offset', description: 'Offset of abilities to sync.')]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-ability', description: 'Usage description')]
  public function syncAbility(array $options = ['limit' => PokeApi::MAX_LIMIT, 'offset' => 0]): void {
    $connection = $this->database->startTransaction();
    $this->logger()->log('notice', 'Synchronizing pokemon abilities...');

    try {
      $this->abilitySync->sync($options['limit'], $options['offset']);
      $this->logger()->log('success', 'Pokemon abilities synchronization successfully');
    }
    catch (\Exception $e) {
      $connection->rollBack();
      $this->logger()->log('error', $this->t('Failed to synchronize pokemon abilities: @message', ['@message' => $e->getMessage()]));
      $this->logger()->log('error', $this->t('Stack trace: @stack_trace', [
        '@stack_trace' => $e->getTraceAsString(),
      ]));

    }
  }

}
