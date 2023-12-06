<?php

namespace Drupal\pokemon_api_sync\Drush\Commands;

use Drupal\pokemon_api_sync\Sync\TypeSync;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TypeSyncCommands.
 */
final class TypeSyncCommands extends DrushCommands {

  /**
   * Constructs a TypeSyncCommands object.
   */
  public function __construct(private readonly TypeSync $typeSync) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('pokemon_api_sync.type_sync'),
    );
  }

  /**
   * Command to synchronize pokemon types.
   */
  #[CLI\Command(name: 'pokemon_api_sync:sync-type', aliases: ['sync-type'])]
  #[CLI\Usage(name: 'pokemon_api_sync:sync-type', description: 'Usage description')]
  public function syncType() {
    $this->typeSync->syncAll();
  }
}
