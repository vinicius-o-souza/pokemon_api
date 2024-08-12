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
   * Constructs a PokemonSync object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerChannelInterface $logger,
    private readonly PokemonApi $pokemonApi
  ) {
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
  private function getDataFields(Pokemon $pokemon): array {
    // $abilities = $this->getPokemonTermsByApiIds('pokemon_ability', $pokemon->getAbilities());
    $stats = $this->getPokemonTermsByApiIds('pokemon_stat', $pokemon->getStats());
    $types = $this->getPokemonTermsByApiIds('pokemon_type', $pokemon->getTypes());

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
      // 'field_pokemon_abilities' => $abilities,
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
   * 
   * @return array
   *   List of pokemon types api IDs.
   */
  private function getPokemonTermsByApiIds(string $vid, array $pokemonApiIds): array {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => $vid,
    ]);

    $pokemonTerms = [];
    foreach ($terms as $term) {
      if (in_array($term->get('field_pokeapi_id')->getString(), $pokemonApiIds)) {
        $pokemonTerms[] = $term->id();
      }
    }

    return $pokemonTerms;
  }

}
