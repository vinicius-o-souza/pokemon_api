<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\Core\Entity\ContentEntityBase;
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
   * List of taxonomy terms needed.
   * 
   * @var array
   */
  private array $taxonomyTerms = [];

  /**
   * Constructs a PokemonSync object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerChannelInterface $logger,
    private readonly PokemonApi $pokemonApi
  ) {
    $this->taxonomyTerms = $this->getAllTermsNeeded();
  }

  private function getAllTermsNeeded(): array {
    $types = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'pokemon_type',
    ]);
    foreach ($types as $key => $type) {
      $types[$type->get('field_pokeapi_id')->getString()] = $type;
    }

    $stats = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'pokemon_stat',
    ]);
    foreach ($stats as $key => $stat) {
      $stats[$stat->get('field_pokeapi_id')->getString()] = $stat;
    }

    $abilities = [];
    // $abilities = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
    //   'vid' => 'pokemon_ability',
    // ]);

    $moves = [];
    // $moves = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
    //   'vid' => 'pokemon_move',
    // ]);

    return [
      'pokemon_type' => $types,
      'pokemon_stat' => $stats,
      'pokemon_ability' => $abilities,
      'pokemon_move' => $moves,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function syncAll(): void {
    $pokemons = $this->pokemonApi->getResourcesPagination(1, 0);

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
    $data = $this->getDataFields($pokemon, $node);
    dd($data);

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
          $node->save();
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
  private function getDataFields(Pokemon $pokemon, ?ContentEntityBase $node): array {
    $stats = $this->getOrCreateStatParagaphs($pokemon->getStats(), $node);
    $types = $this->getTermsByApiIds('pokemon_type', $pokemon->getTypes());

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
      // 'field_pokemon_abilities' => $pokemon->getAbiliities(),
      // 'field_pokemon_moves' => $pokemon->getMoves(),
      // 'field_pokemon_sprites' => $pokemon->getSprites(),
      // 'field_pokemon_species' => $pokemon->getSpecies(),
      'field_pokemon_stats' => $stats,
      'field_pokemon_types' => $types,
    ];
  }

  /**
   * Get array of pokemon api IDs.
   * 
   * @param string $vid
   *   The vid.
   * @param array $resourceApiIds
   *   The resource api ids.
   * 
   * @return array
   *   List of pokemon types api IDs.
   */
  private function getTermsByApiIds(string $vid, array $resourceApiIds): array {
    $terms = [];
    foreach ($this->taxonomyTerms[$vid] as $pokeApiId => $term) {
      if (array_key_exists($pokeApiId, $resourceApiIds)) {
        $terms[] = $term->id();
      }
    }

    return $terms;
  }

  /**
   * Get or create pokemon stats.
   * 
   * @param array $pokemonStats
   *   The stats terms.
   * @param \Drupal\Core\Entity\ContentEntityBase|null $node
   *   The pokemon node.
   * 
   * @return array
   *   The stats.
   */
  private function getOrCreateStatParagaphs(array $pokemonStats, ?ContentEntityBase $node): array {
    $stats = [];
    if ($node) {
      $paragraphs = $node->get('field_pokemon_stats')->referencedEntities();
      foreach ($paragraphs as $paragraph) {
        $statTerm = $paragraph->get('field_pokemon_stat')->entity;
        if ($statTerm) {
          $statPokeApiId = $statTerm->get('field_pokeapi_id')->value;
          if ($statPokeApiId) {
            $paragraph->set('field_pokemon_stat', $this->taxonomyTerms['pokemon_stat'][$statPokeApiId]);
            $paragraph->set('field_pokemon_base_stat', $pokemonStats[$statPokeApiId]);
            $paragraph->save();
  
            $stats[] = $paragraph->id();
            unset($pokemonStats[$statPokeApiId]);
          }
        }
      }
    }

    foreach ($pokemonStats as $key => $stat) {
      if ($this->taxonomyTerms['pokemon_stat'][$key]) {
        $paragraph = $this->entityTypeManager->getStorage('paragraph')->create([
          'type' => 'pokemon_stat',
          'field_pokemon_stat' => $this->taxonomyTerms['pokemon_stat'][$key],
          'field_pokemon_base_stat' => $stat,
        ]);
        $paragraph->save();
        $stats[] = $paragraph->id(); 
      }
    }

    return $stats;
  }

}
