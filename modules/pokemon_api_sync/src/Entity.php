<?php

namespace Drupal\pokemon_api_sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Logger\LoggerChannelTrait;

/**
 * Class Entity.
 */
abstract class Entity {

  use LoggerChannelTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;
  
  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannelInterface $logger) {
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $logger;
  }

  /**
   * Get the storage class.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The storage class.
   */
  abstract protected function getStorageClass(): EntityStorageInterface;

  /**
   * Create a new entity.
   *
   * @param array $data
   *   An array containing the data for the entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityBase|null
   *   The created entity or NULL on failure.
   */
  public function createEntity(array $data)  {
    try {
      $entity = $this->getStorageClass()->create($data);
      $entity->save();

      return $entity;
    } catch (\Exception $e) {
      $this->logger->error('Failed to create entity: @message', ['@message' => $e->getMessage()]);

      return NULL;
    }
  }

  /**
   * Read a entity by its ID.
   *
   * @param int $id
   *   The ID of the entity to read.
   *
   * @return \Drupal\Core\Entity\ContentEntityBase|null
   *   The entity if found, or NULL if not found.
   */
  public function readEntity($id) {
    return $this->getStorageClass()->load($id);
  }

  /**
   * Update an existing entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $entity
   *   The entity entity to update.
   * @param array $data
   *   An array containing the updated data for the entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityBase|bool
   *   The updated entity entity or FALSE on failure.
   */
  public function updateEntity(ContentEntityBase $entity, array $data) {
    try {
      foreach ($data as $field => $value) {
        $entity->set($field, $value);
      }
      $entity->save();

      return $entity;
    } catch (\Exception $e) {
      $this->logger->error('Failed to update entity: @message', ['@message' => $e->getMessage()]);

      return FALSE;
    }
  }

  /**
   * Delete a entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $entity
   *   The entity entity to delete.
   *
   * @return bool
   *   TRUE if the entity is successfully deleted, FALSE on failure.
   */
  public function deleteEntity(ContentEntityBase $entity) {
    try {
      $entity->delete();

      return TRUE;
    } catch (\Exception $e) {
      $this->logger->error('Failed to delete entity: @message', ['@message' => $e->getMessage()]);

      return FALSE;
    }
  }

}