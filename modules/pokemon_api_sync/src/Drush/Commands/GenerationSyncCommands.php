<?php

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api_sync\Sync\GenerationSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sync Pokemon Generation taxonomy.
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
   * Command to synchronize pokemon generation.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-generation', aliases: ['sync-generation'])]
  #[CLI\Option(name: 'limit', description: 'Limit of generations to sync.')]
  #[CLI\Option(name: 'offset', description: 'Offset of generations to sync.')]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-generation', description: 'Command to synchronize pokemon generations.')]
  public function sync(array $options = ['limit' => PokeApi::MAX_LIMIT, 'offset' => 0]): void {
    $connection = $this->database->startTransaction();
    $this->logger()->log('notice', 'Synchronizing pokemon generations...');

    try {
      $this->generationSync->sync($options['limit'], $options['offset']);
      if ($this->logger) {
        $this->logger()->log('success', 'Pokemon generations synchronization successfully');
      }
    }
    catch (\Exception $e) {
      $connection->rollBack();
      if ($this->logger) {
        $this->logger()->log('error', $this->t('Failed to synchronize pokemon generations: @message', ['@message' => $e->getMessage()]));
        $this->logger()->log('error', $this->t('Stack trace: @stack_trace', [
          '@stack_trace' => $e->getTraceAsString(),
        ]));
      }

    }
  }

}
