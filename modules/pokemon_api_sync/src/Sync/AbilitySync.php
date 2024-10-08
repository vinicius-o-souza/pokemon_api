<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\pokemon_api\Endpoints;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api\Resource\Ability;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api_sync\SyncTermEntity;

/**
 * Sync Pokemon Ability taxonomy.
 */
class AbilitySync extends SyncTermEntity {

  /**
   * {@inheritdoc}
   */
  public function sync(int $limit = PokeApi::MAX_LIMIT, int $offset = 0): void {
    $abilities = $this->pokeApi->getResources(Endpoints::ABILITY->value, $limit, $offset);

    foreach ($abilities as $ability) {
      $this->syncResource($ability);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getVid(): string {
    return 'pokemon_ability';
  }

  /**
   * {@inheritdoc}
   */
  protected function getTranslatableFields(ResourceInterface $resource): array {
    if (!$resource instanceof Ability) {
      return [];
    }
    return [
      'name' => $resource->getNames(),
      'description' => $resource->getFlavorText(),
    ];
  }

}
