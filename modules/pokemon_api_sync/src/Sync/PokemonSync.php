<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\pokemon_api\Resource\Pokemon;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api_sync\SyncInterface;
use Drupal\pokemon_api_sync\SyncNodeEntity;

/**
 * Sync Pokemon node.
 */
class PokemonSync extends SyncNodeEntity implements SyncInterface {

  /**
   * List of taxonomy terms needed.
   *
   * @var array
   */
  private array $taxonomyTerms = [];

  /**
   * {@inheritdoc}
   */
  public function syncAll(): void {
    if (empty($this->taxonomyTerms)) {
      $this->taxonomyTerms = $this->getAllTerms();
    }

    $pokemon = new Pokemon();
    $pokemons = $this->pokeApi->getAllResources($pokemon);

    foreach ($pokemons as $pokemon) {
      $this->sync($pokemon);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDataFields(ResourceInterface $resource, ?ContentEntityBase $node): array {
    if (!$resource instanceof Pokemon) {
      throw new \Exception('Invalid resource type.');
    }

    if (empty($this->taxonomyTerms)) {
      $this->taxonomyTerms = $this->getAllTerms();
    }

    $abilites = $this->getTermsByApiIds('pokemon_ability', $resource->getAbilities());
    // $moves = $this->getTermsByApiIds('pokemon_move', $resource->getMoves());
    $stats = $this->getOrCreateStatParagraphs($resource->getStats(), $node);
    $types = $this->getTermsByApiIds('pokemon_type', $resource->getTypes());

    return [
      'type' => 'pokemon',
      'title' => ucfirst($resource->getName()),
      'field_pokeapi_id' => $resource->getId(),
      'field_pokemon_experience' => $resource->getBaseExperience(),
      'field_pokemon_height' => $resource->getHeight(),
      'field_pokemon_order' => $resource->getOrder(),
      'field_pokemon_weight' => $resource->getWeight(),
      'field_pokemon_abilities' => $abilites,
      // 'field_pokemon_moves' => $moves,
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
        $terms[] = $term;
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
  private function getOrCreateStatParagraphs(array $pokemonStats, ?ContentEntityBase $node): array {
    $stats = [];
    if ($node) {
      /** @var \Drupal\paragraphs\Entity\Paragraph[] $paragraphs */
      $paragraphs = $node->get('field_pokemon_stats')->referencedEntities();
      foreach ($paragraphs as $paragraph) {
        $statTerm = $paragraph->get('field_pokemon_stat')->entity;
        if ($statTerm) {
          $statPokeApiId = $statTerm->get('field_pokeapi_id')->value;
          if ($statPokeApiId) {
            $paragraph->set('field_pokemon_base_stat', $pokemonStats[$statPokeApiId]);
            $paragraph->save();

            $stats[] = [
              'target_id' => $paragraph->id(),
              'target_revision_id' => $paragraph->getRevisionId(),
            ];
            unset($pokemonStats[$statPokeApiId]);
          }
        }
      }
    }

    foreach ($pokemonStats as $key => $stat) {
      if ($this->taxonomyTerms['pokemon_stat'][$key]) {
        $stats[] = $this->createStatParagraph($this->taxonomyTerms['pokemon_stat'][$key], $stat);
      }
    }

    return $stats;
  }

  /**
   * Get all taxonomy terms needed.
   *
   * @return array
   *   List of taxonomy terms needed.
   */
  private function getAllTerms(): array {
    $types = $this->getTermsByVid('pokemon_type');
    $stats = $this->getTermsByVid('pokemon_stat');
    $abilities = $this->getTermsByVid('pokemon_ability');

    return [
      'pokemon_type' => $types,
      'pokemon_stat' => $stats,
      'pokemon_ability' => $abilities,
    ];
  }

  /**
   * Create stat paragraph.
   *
   * @param int $termId
   *   The term id.
   * @param int $stat
   *   The stat.
   *
   * @return array
   *   The stat paragraph.
   */
  private function createStatParagraph(int $termId, int $stat): array {
    $paragraph = $this->entityTypeManager->getStorage('paragraph')->create([
      'type' => 'pokemon_stats',
      'field_pokemon_stat' => $termId,
      'field_pokemon_base_stat' => $stat,
    ]);
    $paragraph->save();

    return [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
  }

}
