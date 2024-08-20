<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\pokemon_api\Resource\Move;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api_sync\SyncInterface;
use Drupal\pokemon_api_sync\SyncTermEntity;

/**
 * Sync Pokemon Move taxonomy.
 */
class MoveSync extends SyncTermEntity implements SyncInterface {

  /**
   * {@inheritdoc}
   */
  public function syncAll(): void {
    $move = new Move();
    $moves = $this->pokeApi->getAllResources($move);

    foreach ($moves as $move) {
      $this->sync($move);
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
      $data['field_effect_change'] = $resource->getEffectChance();
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
