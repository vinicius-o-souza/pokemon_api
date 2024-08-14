<?php

namespace Drupal\pokemon_api_sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\pokemon_api\Resource\ResourceInterface;

/**
 * Sync taxonomy term entity.
 */
abstract class SyncTermEntity extends SyncEntity {

  /**
   * Get vocabulary id.
   *
   * @return string
   *   Vocabulary id.
   */
  abstract public function getVid(): string;

  /**
   * {@inheritdoc}
   */
  public function getStorageClass(): EntityStorageInterface {
    return $this->entityTypeManager->getStorage('taxonomy_term');
  }

  /**
   * {@inheritdoc}
   */
  public function readEntity($id): ?ContentEntityBase {
    $entities = $this->getStorageClass()->loadByProperties([
      'vid' => $this->getVid(),
      'field_pokeapi_id' => $id,
    ]);

    $entity = array_shift($entities);
    if ($entity instanceof ContentEntityBase) {
      return $entity;
    }

    return NULL;
  }

  /**
   * Get data fields.
   *
   * @param \Drupal\pokemon_api\Resource\ResourceInterface $resource
   *   Resource.
   *
   * @return array
   *   Data fields.
   */
  protected function getDataFields(ResourceInterface $resource): array {
    return [
      'name' => ucfirst($resource->getName()),
      'vid' => $this->getVid(),
      'field_pokeapi_id' => $resource->getId(),
    ];
  }

}
