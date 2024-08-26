<?php

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api_sync\Sync\TypeSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sync Pokemon Type taxonomy.
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
   * Command to synchronize pokemon types.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-type', aliases: ['sync-type'])]
  #[CLI\Option(name: 'limit', description: 'Limit of types to sync.')]
  #[CLI\Option(name: 'offset', description: 'Offset of types to sync.')]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-type', description: 'Usage description')]
  public function syncType(array $options = ['limit' => PokeApi::MAX_LIMIT, 'offset' => 0]): void {
    $connection = $this->database->startTransaction();
    $this->logger()->log('notice', 'Synchronizing pokemon types...');

    try {
      $this->typeSync->sync($options['limit'], $options['offset']);
      if ($this->logger) {
        $this->logger()->log('success', 'Pokemon types synchronization successfully');
      }
    }
    catch (\Exception $e) {
      $connection->rollBack();
      if ($this->logger) {
        $this->logger()->log('error', $this->t('Failed to synchronize pokemon types: @message', ['@message' => $e->getMessage()]));
        $this->logger()->log('error', $this->t('Stack trace: @stack_trace', [
          '@stack_trace' => $e->getTraceAsString(),
        ]));
      }

    }
  }

}
