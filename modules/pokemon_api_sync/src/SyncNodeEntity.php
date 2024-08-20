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
  public function sync(ResourceInterface $resource): void {
    $resource = $this->pokeApi->getResource($resource);

    if (!$resource->getId()) {
      return;
    }

    $node = $this->readEntity($resource->getId());
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
