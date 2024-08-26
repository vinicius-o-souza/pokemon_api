<?php

namespace Drupal\pokemon_api_sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\taxonomy\Entity\Term;

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
   * Get translatable fields.
   *
   * @param \Drupal\pokemon_api\Resource\ResourceInterface $resource
   *   The resource.
   *
   * @return array
   *   Translatable fields.
   */
  abstract protected function getTranslatableFields(ResourceInterface $resource): array;

  /**
   * {@inheritdoc}
   */
  public function getStorageClass(): EntityStorageInterface {
    return $this->entityTypeManager->getStorage('taxonomy_term');
  }

  /**
   * {@inheritdoc}
   */
  public function syncResource(ResourceInterface $resource): void {
    $this->logger->info('Syncing resource {endpoint}: {resource}', [
      'endpoint' => $resource->getEndpoint(),
      'resource' => $resource->getId(),
    ]);
    $resource = $this->pokeApi->getResource($resource->getEndpoint(), $resource->getId());

    if (!$resource->getId()) {
      $this->logger->info('Resource {endpoint} not found: {resource}', [
        'endpoint' => $resource->getEndpoint(),
        'resource' => $resource->getId(),
      ]);
      return;
    }

    $term = $this->readEntityByPokeId($resource->getId());
    $this->syncTerm($resource, $term);
  }

  /**
   * Syncs a term with the provided resource.
   *
   * @param ResourceInterface $resource
   *   The resource to sync with.
   * @param ?ContentEntityBase $term
   *   The term to sync.
   *
   * @return void
   */
  public function syncTerm(ResourceInterface $resource, ContentEntityBase $term = NULL): void {
    $data = $this->getDataFields($resource);

    if ($term) {
      $term = $this->updateEntity($term, $data);
    }
    else {
      $term = $this->createEntity($data);
    }

    if ($term) {
      $translatableFields = $this->getTranslatableFields($resource);
      $term = $this->addTranslation($term, $translatableFields);
      $term->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function readEntityByPokeId(int $pokeApiId): ?ContentEntityBase {
    $entities = $this->getStorageClass()->loadByProperties([
      'vid' => $this->getVid(),
      'field_pokeapi_id' => $pokeApiId,
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
