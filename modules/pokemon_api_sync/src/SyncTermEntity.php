<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\pokemon_api\Resource\ResourceInterface;

/**
 * Base class for syncing taxonomy term entities.
 */
abstract class SyncTermEntity extends SyncEntity {

  /**
   * Gets the vocabulary ID.
   *
   * @return string
   *   The vocabulary machine name.
   */
  abstract public function getVid(): string;

  /**
   * Gets translatable fields for the resource.
   *
   * @param \Drupal\pokemon_api\Resource\ResourceInterface $resource
   *   The resource.
   *
   * @return array
   *   Translatable fields with Translation objects.
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
   * Syncs a taxonomy term with the provided resource.
   *
   * @param \Drupal\pokemon_api\Resource\ResourceInterface $resource
   *   The resource to sync with.
   * @param \Drupal\Core\Entity\ContentEntityBase|null $term
   *   The existing term, or NULL to create a new one.
   */
  public function syncTerm(ResourceInterface $resource, ?ContentEntityBase $term = NULL): void {
    $data = $this->getDataFields($resource);

    $term = $term ? $this->updateEntity($term, $data) : $this->createEntity($data);
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
    return $entity instanceof ContentEntityBase ? $entity : NULL;
  }

  /**
   * Gets the base data fields for a term.
   *
   * @param \Drupal\pokemon_api\Resource\ResourceInterface $resource
   *   The resource.
   *
   * @return array
   *   The field data.
   */
  protected function getDataFields(ResourceInterface $resource): array {
    return [
      'name' => ucfirst($resource->getName()),
      'vid' => $this->getVid(),
      'field_pokeapi_id' => $resource->getId(),
    ];
  }

}
