<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\pokemon_api\ApiResource\MoveApi;
use Drupal\pokemon_api\Resource\Move;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api_sync\SyncInterface;
use Drupal\pokemon_api_sync\SyncTermEntity;

/**
 * Sync Pokemon Move taxonomy.
 */
class MoveSync extends SyncTermEntity implements SyncInterface {

  /**
   * Constructs a MoveSync object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerChannelInterface $logger,
    private readonly MoveApi $moveApi,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function syncAll(): void {
    $moves = $this->moveApi->getAllResources();

    foreach ($moves as $move) {
      $this->sync($move);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function sync(ResourceInterface $move): void {
    $move = $this->moveApi->getResource($move->getId());

    $term = $this->readEntity($move->getId());
    $data = $this->getDataFields($move);

    if ($term) {
      $term = $this->updateEntity($term, $data);
    }
    else {
      $term = $this->createEntity($data);
    }

    if ($term && $move instanceof Move) {
      $translatableFields = $this->getTranslatableFields($move);
      $term = $this->addTranslation($term, $translatableFields);
      $term->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getVid(): string {
    return 'pokemon_move';
  }

  /**
   * {@inheritdoc}
   */
  private function getTranslatableFields(Move $move): array {
    return [
      'name' => $move->getNames(),
      'description' => $move->getFlavorText(),
    ];
  }

}
