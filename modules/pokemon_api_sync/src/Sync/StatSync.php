<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\pokemon_api\Endpoints;
use Drupal\pokemon_api\PokeApiInterface;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api\Resource\Stat;
use Drupal\pokemon_api_sync\SyncTermEntity;

/**
 * Syncs Pokémon stats to taxonomy terms.
 */
class StatSync extends SyncTermEntity {

  /**
   * {@inheritdoc}
   */
  public function sync(int $limit = PokeApiInterface::MAX_LIMIT, int $offset = 0): void {
    $stats = $this->pokeApi->getResources(Endpoints::Stat->value, $limit, $offset);

    foreach ($stats as $stat) {
      $this->syncResource($stat);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getVid(): string {
    return 'pokemon_stat';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTranslatableFields(ResourceInterface $resource): array {
    if (!$resource instanceof Stat) {
      return [];
    }

    return [
      'name' => $resource->getNames(),
    ];
  }

}
