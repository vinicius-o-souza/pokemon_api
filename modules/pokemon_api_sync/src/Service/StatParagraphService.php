<?php

namespace Drupal\pokemon_api_sync\Service;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Service to manage stat paragraphs.
 */
class StatParagraphService {

  /**
   * List of stat terms.
   *
   * @var array
   */
  private array $statTerms = [];

  /**
   * Constructs a new StatParagraphService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(private readonly EntityTypeManager $entityTypeManager) {
    $this->statTerms = $this->getStatTerms();
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
  public function getOrCreateStatParagraphs(array $pokemonStats, ?ContentEntityBase $node): array {
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
      if ($this->statTerms['pokemon_stat'][$key]) {
        $stats[] = $this->createStatParagraph($this->statTerms['pokemon_stat'][$key], $stat);
      }
    }

    return $stats;
  }

  /**
   * Retrieves the list of stat terms from the taxonomy storage.
   *
   * @return array
   *   Array of stats where the key is the PokeAPI ID and the value term ID.
   */
  private function getStatTerms(): array {
    /** @var \Drupal\taxonomy\Entity\Term[] $terms */
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'pokemon_stat',
    ]);

    $statTerms = [];
    foreach ($terms as $term) {
      $statTerms[$term->get('field_pokeapi_id')->getString()] = $term->id();
    }

    return $statTerms;
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
