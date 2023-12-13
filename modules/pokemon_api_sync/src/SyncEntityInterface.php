<?php

namespace Drupal\pokemon_api_sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Interface to sync entities.
 */
interface SyncEntityInterface {

  /**
   * Get the storage class.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The storage class.
   */
  public function getStorageClass(): EntityStorageInterface;

  /**
   * Create a new entity.
   *
   * @param array $data
   *   The data to create the entity with.
   *
   * @return \Drupal\Core\Entity\ContentEntityBase|null
   *   The created entity or NULL on failure.
   */
  public function createEntity(array $data): ?ContentEntityBase;

  /**
   * Read a entity by its ID.
   *
   * @param int $id
   *   The ID of the entity to read.
   *
   * @return \Drupal\Core\Entity\ContentEntityBase|null
   *   The entity if found, or NULL if not found.
   */
  public function readEntity($id): ?ContentEntityBase;

  /**
   * Update an existing entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $entity
   *   The entity entity to update.
   * @param array $data
   *   The data to update the entity with.
   *
   * @return \Drupal\Core\Entity\ContentEntityBase|null
   *   The updated entity entity or FALSE on failure.
   */
  public function updateEntity(ContentEntityBase $entity, array $data): ?ContentEntityBase;

  /**
   * Delete a entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $entity
   *   The entity entity to delete.
   *
   * @return bool
   *   TRUE if the entity is successfully deleted, FALSE on failure.
   */
  public function deleteEntity(ContentEntityBase $entity): bool;

}
