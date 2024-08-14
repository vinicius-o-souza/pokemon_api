<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\pokemon_api\ApiResource\StatApi;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api\Resource\Stat;
use Drupal\pokemon_api_sync\SyncInterface;
use Drupal\pokemon_api_sync\SyncTermEntity;

/**
 * Sync Pokemon Stat taxonomy.
 */
class StatSync extends SyncTermEntity implements SyncInterface {

  /**
   * Constructs a StatSync object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerChannelInterface $logger,
    private readonly StatApi $statApi,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function syncAll(): void {
    $stats = $this->statApi->getAllResources();

    foreach ($stats as $stat) {
      $this->sync($stat);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function sync(ResourceInterface $stat): void {
    $stat = $this->statApi->getResource($stat->getId());

    $term = $this->readEntity($stat->getId());
    $data = $this->getDataFields($stat);

    if ($term) {
      $term = $this->updateEntity($term, $data);
    }
    else {
      $term = $this->createEntity($data);
    }

    if ($term && $stat instanceof Stat) {
      $translatableFields = $this->getTranslatableFields($stat);
      $term = $this->addTranslation($term, $translatableFields);
      $term->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getVid(): string {
    return 'pokemon_stat';
  }

  /**
   * {@inheritdoc}
   */
  private function getTranslatableFields(Stat $stat): array {
    return [
      'name' => $stat->getNames(),
    ];
  }

}
