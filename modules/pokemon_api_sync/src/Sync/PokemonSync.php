<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\pokemon_api\ApiResource\PokemonApi;
use Drupal\pokemon_api\Resource\Resource;
use Drupal\pokemon_api\Resource\Pokemon;
use Drupal\pokemon_api_sync\SyncInterface;
use Drupal\pokemon_api_sync\SyncNodeEntity;

/**
 * Sync Pokemon node.
 */
class PokemonSync extends SyncNodeEntity implements SyncInterface {

  /**
   * Order maximum.
   * 
   * @var int
   * 
   */
  private const ORDER_MAXIMUM = 10000;

  /**
   * List of pokemon types api IDs.
   * 
   * @var array
   */
  protected array $pokemonTypesApiIds = [];

  /**
   * Constructs a PokemonSync object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerChannelInterface $logger,
    private readonly PokemonApi $pokemonApi
  ) {
    $this->pokemonTypesApiIds = $this->getPokemonTypesApiId();
  }

  /**
   * {@inheritdoc}
   */
  public function syncAll(): void {
    $pokemons = $this->pokemonApi->getResourcesPagination(2000, 0);

    foreach ($pokemons as $pokemon) {
      $this->sync($pokemon);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function sync(Resource $pokemon): void {
    $pokemon = $this->pokemonApi->getResource($pokemon->getId());

    $node = $this->readEntity($pokemon->getId());
    $data = $this->getDataFields($pokemon);

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
            'title' => $pokemon->getName(),
          ]);
        }
        else {
          $translationNode = $node->getTranslation($language);
          $translationNode->set('title', $pokemon->getName());
          $translationNode->save();
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  private function getDataFields(Pokemon $pokemon): array {
    $types = $this->getPokemonTypesId($pokemon->getTypes());
    if ($pokemon->getOrder() < 0) {
      $pokemon->setOrder(self::ORDER_MAXIMUM + $pokemon->getOrder());
    }
    return [
      'type' => 'pokemon',
      'title' => ucfirst($pokemon->getName()),
      'field_pokeapi_id' => $pokemon->getId(),
      'field_pokemon_experience' => $pokemon->getBaseExperience(),
      'field_pokemon_height' => $pokemon->getHeight(),
      'field_pokemon_order' => $pokemon->getOrder(),
      'field_pokemon_weight' => $pokemon->getWeight(),
      // 'field_pokemon_abilities' => $pokemon->getAbilities(),
      // 'field_pokemon_moves' => $pokemon->getMoves(),
      // 'field_pokemon_sprites' => $pokemon->getSprites(),
      // 'field_pokemon_species' => $pokemon->getSpecies(),
      // 'field_pokemon_stats' => $pokemon->getStats(),
      'field_pokemon_types' => $types,
    ];
  }

  /**
   * Get array of pokemon api IDs.
   * 
   * @return array
   *   List of pokemon types api IDs.
   */
  private function getPokemonTypesApiId(): array {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'pokemon_type',
    ]);

    $pokemonTypesApiIds = [];
    foreach ($terms as $term) {
      $pokemonTypesApiIds[$term->id()] = $term->get('field_pokeapi_id')->getString();
    }

    return $pokemonTypesApiIds;
  }

  
  /**
   * Get list of pokemon types term IDs.
   *
   * @param array $types
   *   An array of Pokemon type terms from the PokeAPI.
   * 
   * @return array
   *   An array of Pokemon type term IDs.
   */
  private function getPokemonTypesId(array $types): array {
    $pokeApiIds = array_map(function ($type) {
      $url = $type['type']['url'];
      return Resource::extractIdFromUrl($url);
    }, $types);

    $typeIds = [];
    foreach ($this->pokemonTypesApiIds as $termId => $pokemonApiId) {
      $key = array_search($pokemonApiId, $pokeApiIds);
      if ($key !== false) {
        $typeIds[] = $termId;
      }
    }

    return $typeIds;
  }

}
