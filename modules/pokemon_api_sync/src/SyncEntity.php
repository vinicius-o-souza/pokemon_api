<?php

namespace Drupal\pokemon_api_sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\pokemon_api\Translation;

/**
 * Class Entity.
 */
abstract class SyncEntity implements SyncEntityInterface {

  /**
   * Constructs a SyncEntity object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerChannelInterface $logger
  ) {}

  /**
   * {@inheritdoc}
   */
  public function createEntity(array $data): ?ContentEntityBase {
    try {
      $entity = $this->getStorageClass()->create($data);
      $entity->save();

      if ($entity instanceof ContentEntityBase) {
        return $entity;
      }

      return NULL;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to create entity: @message', ['@message' => $e->getMessage()]);

      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function readEntity($id): ?ContentEntityBase {
    $entities = $this->getStorageClass()->loadByProperties([
      'field_pokeapi_id' => $id,
    ]);

    $entity = array_shift($entities);
    if ($entity instanceof ContentEntityBase) {
      return $entity;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function updateEntity(ContentEntityBase $entity, array $data): ?ContentEntityBase {
    try {
      foreach ($data as $field => $value) {
        $entity->set($field, $value);
      }

      $entity->save();

      return $entity;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to update entity: @message', ['@message' => $e->getMessage()]);

      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteEntity(ContentEntityBase $entity): bool {
    try {
      $entity->delete();

      return TRUE;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to delete entity: @message', ['@message' => $e->getMessage()]);

      return FALSE;
    }
  }

  /**
   * Adds translations to a content entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $entity
   *   The content entity to add translations to.
   * @param array $fields
   *   An array of fields containing translations.
   *
   * @return \Drupal\Core\Entity\ContentEntityBase
   *   The content entity with added translations.
   */
  protected function addTranslation(ContentEntityBase $entity, array $fields): ContentEntityBase {
    foreach ($fields as $key => $field) {
      if ($field instanceof Translation) {
        $languages = [
          Translation::ES_LANGUAGE => 'es',
          Translation::PT_BR_LANGUAGE => 'pt-br',
        ];

        foreach ($languages as $pokeApiLanguage => $drupalLanguage) {
          if ($field->getValue($pokeApiLanguage)) {
            if (!$entity->hasTranslation($drupalLanguage)) {
              $entity->addTranslation($drupalLanguage, [
                $key => $field->getValue($pokeApiLanguage),
              ]);
            }
            else {
              $translationEntity = $entity->getTranslation($drupalLanguage);
              $translationEntity->set($key, $field->getValue($pokeApiLanguage));
              $translationEntity->save();
            }
          }
        }
      }
    }

    return $entity;
  }

}
