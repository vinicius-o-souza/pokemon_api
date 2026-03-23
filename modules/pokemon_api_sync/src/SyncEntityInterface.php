<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Interface for entity sync operations.
 */
interface SyncEntityInterface {

  /**
   * Gets the entity storage handler.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The storage handler.
   */
  public function getStorageClass(): EntityStorageInterface;

  /**
   * Creates a new entity.
   *
   * @param array $data
   *   The entity data.
   *
   * @return \Drupal\Core\Entity\ContentEntityBase|null
   *   The created entity, or NULL on failure.
   */
  public function createEntity(array $data): ?ContentEntityBase;

  /**
   * Reads an entity by its PokeAPI ID.
   *
   * @param int $pokeApiId
   *   The PokeAPI ID.
   *
   * @return \Drupal\Core\Entity\ContentEntityBase|null
   *   The entity, or NULL if not found.
   */
  public function readEntityByPokeId(int $pokeApiId): ?ContentEntityBase;

  /**
   * Updates an existing entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $entity
   *   The entity to update.
   * @param array $data
   *   The data to set.
   *
   * @return \Drupal\Core\Entity\ContentEntityBase|null
   *   The updated entity, or NULL on failure.
   */
  public function updateEntity(ContentEntityBase $entity, array $data): ?ContentEntityBase;

  /**
   * Deletes an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $entity
   *   The entity to delete.
   *
   * @return bool
   *   TRUE on success, FALSE on failure.
   */
  public function deleteEntity(ContentEntityBase $entity): bool;

}
