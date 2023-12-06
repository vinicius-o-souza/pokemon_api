<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\pokemon_api\ApiResource\TypeApi;
use Drupal\pokemon_api\Resource\Type;
use Drupal\pokemon_api_sync\TermEntity;

/**
 * Class TypeSync.
 */
class TypeSync {

  /**
   * Constructs a TypeSync object.
   */
  public function __construct(
    private readonly TermEntity $termEntity,
    private readonly TypeApi $typeApi
  ) {}

  /**
   * Command to sync pokemon types.
   */
  public function syncAll(): void {
    $endpoint = Type::getEndpoint();
    $types = $this->typeApi->getAllResources($endpoint);

    foreach ($types as $type) {
      $this->sync($type);
    }
  }


   /**
   * Synchronizes a Type object.
   *
   * @param Type $type
   *  The Type object to synchronize.
   * @return void
   * @throws \Exception
   */
  public function sync(Type $type): void {
    $type = $this->typeApi->getResource($type->getId());
    
    $data = [];
    $term = $this->termEntity->readEntity($type->getId());
    if ($term) {
      $this->termEntity->updateEntity($term, $data);
    }
    else {
      $this->termEntity->createEntity($data);
    }
  }
  
}