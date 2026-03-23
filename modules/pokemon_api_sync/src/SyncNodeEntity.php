<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\pokemon_api\Resource\ResourceInterface;

/**
 * Base class for syncing node entities.
 */
abstract class SyncNodeEntity extends SyncEntity {

  /**
   * Gets the content type machine name.
   *
   * @return string
   *   The content type.
   */
  abstract public function getContentType(): string;

  /**
   * Gets the data fields for creating/updating a node.
   *
   * @param \Drupal\pokemon_api\Resource\ResourceInterface $resource
   *   The resource.
   * @param \Drupal\Core\Entity\ContentEntityBase|null $node
   *   The existing node, or NULL for new nodes.
   *
   * @return array
   *   The field data.
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
   * @param \Drupal\Core\Entity\ContentEntityBase|null $node
   *   The existing node, or NULL to create a new one.
   */
  public function syncNode(ResourceInterface $resource, ?ContentEntityBase $node = NULL): void {
    $data = $this->getDataFields($resource, $node);

    $node = $node ? $this->updateEntity($node, $data) : $this->createEntity($data);
    if (!$node || !$resource->getName()) {
      return;
    }

    if (!$node->isTranslatable()) {
      return;
    }

    $languages = ['es', 'pt-br'];
    foreach ($languages as $language) {
      if (!$node->hasTranslation($language)) {
        $node->addTranslation($language, ['title' => $resource->getName()]);
        $node->save();
      }
      else {
        $translationNode = $node->getTranslation($language);
        $translationNode->set('title', $resource->getName());
        $translationNode->save();
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
    return $entity instanceof ContentEntityBase ? $entity : NULL;
  }

  /**
   * Gets taxonomy terms keyed by PokeAPI ID.
   *
   * @param string $vid
   *   The vocabulary ID.
   *
   * @return array
   *   Terms keyed by PokeAPI ID with term ID as value.
   */
  protected function getTermsByVid(string $vid): array {
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    /** @var \Drupal\taxonomy\Entity\Term[] $terms */
    $terms = $storage->loadByProperties(['vid' => $vid]);

    $result = [];
    foreach ($terms as $term) {
      $result[$term->get('field_pokeapi_id')->getString()] = $term->id();
    }

    return $result;
  }

}
