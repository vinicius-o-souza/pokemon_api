<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\pokemon_api\ApiResource\AbilityApi;
use Drupal\pokemon_api\Resource\Ability;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api_sync\SyncInterface;
use Drupal\pokemon_api_sync\SyncTermEntity;

/**
 * Sync Pokemon Ability taxonomy.
 */
class AbilitySync extends SyncTermEntity implements SyncInterface {

  /**
   * Constructs a AbilitySync object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerChannelInterface $logger,
    private readonly AbilityApi $abilityApi,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function syncAll(): void {
    $abilities = $this->abilityApi->getAllResources();

    foreach ($abilities as $ability) {
      $this->sync($ability);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function sync(ResourceInterface $ability): void {
    $ability = $this->abilityApi->getResource($ability->getId());

    $term = $this->readEntity($ability->getId());
    $data = $this->getDataFields($ability);

    if ($term) {
      $term = $this->updateEntity($term, $data);
    }
    else {
      $term = $this->createEntity($data);
    }

    if ($term && $ability instanceof Ability) {
      $translatableFields = $this->getTranslatableFields($ability);
      $term = $this->addTranslation($term, $translatableFields);
      $term->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getVid(): string {
    return 'pokemon_ability';
  }

  /**
   * {@inheritdoc}
   */
  private function getTranslatableFields(Ability $ability): array {
    return [
      'name' => $ability->getNames(),
      'body' => $ability->getEffectEntries(),
    ];
  }

}
