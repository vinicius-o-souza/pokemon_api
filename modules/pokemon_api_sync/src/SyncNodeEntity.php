<?php

namespace Drupal\pokemon_api_sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\pokemon_api\Resource\ResourceInterface;

/**
 * Sync node entity.
 */
abstract class SyncNodeEntity extends SyncEntity {

  /**
   * Get the content type.
   *
   * @return string
   *   The content type.
   */
  abstract public function getContentType(): string;

  /**
   * Retrieves the data fields.
   *
   * @param \Drupal\pokemon_api\Resource\ResourceInterface $resource
   *   The resource.
   * @param \Drupal\Core\Entity\ContentEntityBase|null $node
   *   The content entity.
   *
   * @return array
   *   The data fields.
   */
  abstract protected function getDataFields(ResourceInterface $resource, ?ContentEntityBase $node): array;

  /**
   * {@inheritdoc}
   */
  public function getStorageClass(): EntityStorageInterface {
    return $this->entityTypeManager->getStorage('node');
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

    $node = $this->readEntityByPokeId($resource->getId());
    $this->syncNode($resource, $node);
  }

  /**
   * Syncs a node with the provided resource.
   *
   * @param \Drupal\pokemon_api\Resource\ResourceInterface $resource
   *   The resource to sync with.
   * @param ?ContentEntityBase $node
   *   The node to sync.
   */
  public function syncNode(ResourceInterface $resource, ContentEntityBase $node = NULL): void {
    $data = $this->getDataFields($resource, $node);

    if ($node) {
      $node = $this->updateEntity($node, $data);
    }
    else {
      $node = $this->createEntity($data);
    }

    if ($node) {
      $languages = [
        'es',
        'pt-br',
      ];

      foreach ($languages as $language) {
        if ($resource->getName()) {
          if (!$node->hasTranslation($language)) {
            $node->addTranslation($language, [
              'title' => $resource->getName(),
            ]);
            $node->save();
          }
          else {
            $translationNode = $node->getTranslation($language);
            $translationNode->set('title', $resource->getName());
            $translationNode->save();
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function readEntityByPokeId(int $pokeApiId): ?ContentEntityBase {
    $entities = $this->getStorageClass()->loadByProperties([
      'type' => $this->getContentType(),
      'field_pokeapi_id' => $pokeApiId,
    ]);

    $entity = array_shift($entities);
    if ($entity instanceof ContentEntityBase) {
      return $entity;
    }

    return NULL;
  }

  /**
   * Retrieves an array of terms based on the provided vocabulary ID.
   *
   * @param string $vid
   *   The vocabulary ID.
   *
   * @return array
   *   Array of terms where the key is the PokeAPI ID and the value is the tid.
   */
  protected function getTermsByVid(string $vid): array {
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    /** @var \Drupal\taxonomy\Entity\Term[] $terms */
    $terms = $storage->loadByProperties([
      'vid' => $vid,
    ]);

    foreach ($terms as $key => $term) {
      $terms[$term->get('field_pokeapi_id')->getString()] = $term->id();
    }

    return $terms;
  }

}
