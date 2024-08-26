<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\pokemon_api\Endpoints;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api\Resource\Move;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api_sync\SyncTermEntity;

/**
 * Sync Pokemon Move taxonomy.
 */
class MoveSync extends SyncTermEntity {

  /**
   * {@inheritdoc}
   */
  public function sync(int $limit = PokeApi::MAX_LIMIT, int $offset = 0): void {
    $moves = $this->pokeApi->getResources(Endpoints::MOVE->value, $limit, $offset);

    foreach ($moves as $move) {
      $this->syncResource($move);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getVid(): string {
    return 'pokemon_move';
  }

  /**
   * {@inheritdoc}
   */
  protected function getDataFields(ResourceInterface $resource): array {
    $data = parent::getDataFields($resource);

    if ($resource instanceof Move) {
      $data['field_accuracy'] = $resource->getAccuracy();
      $data['field_effect_chance'] = $resource->getEffectChance();
      $data['field_power'] = $resource->getPower();
      $data['field_power_points'] = $resource->getPowerPoints();
      $data['field_priority'] = $resource->getPriority();

      $type = $resource->getType();
      $type = $this->getStorageClass()->loadByProperties([
        'vid' => 'pokemon_type',
        'field_pokeapi_id' => $type->getId(),
      ]);

      $data['field_type'] = $type;
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  protected function getTranslatableFields(ResourceInterface $resource): array {
    if (!$resource instanceof Move) {
      return [];
    }
    return [
      'name' => $resource->getNames(),
      'description' => $resource->getFlavorText(),
    ];
  }

}
