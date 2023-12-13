<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\pokemon_api\ApiResource\TypeApi;
use Drupal\pokemon_api\Resource\Resource;
use Drupal\pokemon_api\Resource\Type;
use Drupal\pokemon_api_sync\SyncInterface;
use Drupal\pokemon_api_sync\SyncTermEntity;

/**
 * Sync Pokemon Type taxonomy.
 */
class TypeSync extends SyncTermEntity implements SyncInterface {

  /**
   * Constructs a TypeSync object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerChannelInterface $logger,
    private readonly TypeApi $typeApi
  ) {}

  /**
   * {@inheritdoc}
   */
  public function syncAll(): void {
    $types = $this->typeApi->getAllResources();

    foreach ($types as $type) {
      $this->sync($type);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function sync(Resource $type): void {
    $type = $this->typeApi->getResource($type->getId());

    $term = $this->readEntity($type->getId());
    $data = $this->getDataFields($type);

    if ($term) {
      $term = $this->updateEntity($term, $data);
    }
    else {
      $term = $this->createEntity($data);
    }

    if ($term) {
      $translatableFields = $this->getTranslatableFields($type);
      $term = $this->addTranslation($term, $translatableFields);
      $term->save(); 
    }
  }

  /**
   * {@inheritdoc}
   */
  private function getDataFields(Type $type): array {
    return [
      'name' => ucfirst($type->getName()),
      'vid' => 'pokemon_type',
      'field_pokeapi_id' => $type->getId(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  private function getTranslatableFields(Type $type): array {
    return [
      'name' => $type->getNames(),
    ];
  }

}
