<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\pokemon_api\PokeApiInterface;
use Drupal\pokemon_api\Translation;

/**
 * Abstract base class for entity sync operations.
 */
abstract class SyncEntity implements SyncEntityInterface, SyncInterface {

  /**
   * Constructs a SyncEntity object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger channel.
   * @param \Drupal\pokemon_api\PokeApiInterface $pokeApi
   *   The PokeAPI client.
   */
  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly LoggerChannelInterface $logger,
    protected readonly PokeApiInterface $pokeApi,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function createEntity(array $data): ?ContentEntityBase {
    try {
      $entity = $this->getStorageClass()->create($data);
      $entity->save();

      return $entity instanceof ContentEntityBase ? $entity : NULL;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to create entity: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
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
   *   The entity to add translations to.
   * @param array $fields
   *   Fields containing Translation objects keyed by field name.
   *
   * @return \Drupal\Core\Entity\ContentEntityBase
   *   The entity with translations applied.
   */
  protected function addTranslation(ContentEntityBase $entity, array $fields): ContentEntityBase {
    $languageMap = [
      Translation::EN_LANGUAGE => 'en',
      Translation::ES_LANGUAGE => 'es',
      Translation::PT_BR_LANGUAGE => 'pt-br',
    ];

    foreach ($fields as $key => $field) {
      if (!$field instanceof Translation) {
        continue;
      }

      foreach ($languageMap as $pokeApiLanguage => $drupalLanguage) {
        $value = $field->getValue($pokeApiLanguage);
        if ($value === NULL) {
          continue;
        }

        if (!$entity->hasTranslation($drupalLanguage)) {
          $entity->addTranslation($drupalLanguage, [$key => $value]);
        }
        else {
          $translationEntity = $entity->getTranslation($drupalLanguage);
          $translationEntity->set($key, $value);
          $translationEntity->save();
        }
      }
    }

    return $entity;
  }

}
