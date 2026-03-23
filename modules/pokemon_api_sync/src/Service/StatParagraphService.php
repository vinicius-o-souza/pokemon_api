<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Service;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Manages stat paragraph entities.
 */
class StatParagraphService {

  /**
   * Cached stat terms keyed by PokeAPI ID.
   *
   * @var array<string, int>
   */
  private array $statTerms = [];

  /**
   * Constructs a StatParagraphService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(private readonly EntityTypeManagerInterface $entityTypeManager) {
    $this->statTerms = $this->loadStatTerms();
  }

  /**
   * Gets or creates stat paragraphs for a Pokémon.
   *
   * @param array $pokemonStats
   *   Stats keyed by PokeAPI ID.
   * @param \Drupal\Core\Entity\ContentEntityBase|null $node
   *   The existing Pokémon node, or NULL.
   *
   * @return array
   *   Paragraph reference arrays.
   */
  public function getOrCreateStatParagraphs(array $pokemonStats, ?ContentEntityBase $node): array {
    $stats = [];

    if ($node) {
      /** @var \Drupal\paragraphs\Entity\Paragraph[] $paragraphs */
      $paragraphs = $node->get('field_pokemon_stats')->referencedEntities();
      foreach ($paragraphs as $paragraph) {
        $statTerm = $paragraph->get('field_pokemon_stat')->entity;
        if (!$statTerm) {
          continue;
        }

        $statPokeApiId = $statTerm->get('field_pokeapi_id')->value;
        if (!$statPokeApiId || !isset($pokemonStats[$statPokeApiId])) {
          continue;
        }

        $paragraph->set('field_pokemon_base_stat', $pokemonStats[$statPokeApiId]);
        $paragraph->save();

        $stats[] = [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ];
        unset($pokemonStats[$statPokeApiId]);
      }
    }

    foreach ($pokemonStats as $key => $stat) {
      if (isset($this->statTerms[$key])) {
        $stats[] = $this->createStatParagraph($this->statTerms[$key], $stat);
      }
    }

    return $stats;
  }

  /**
   * Loads stat terms from taxonomy storage.
   *
   * @return array<string, int>
   *   Term IDs keyed by PokeAPI ID.
   */
  private function loadStatTerms(): array {
    /** @var \Drupal\taxonomy\Entity\Term[] $terms */
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'pokemon_stat',
    ]);

    $statTerms = [];
    foreach ($terms as $term) {
      $statTerms[$term->get('field_pokeapi_id')->getString()] = (int) $term->id();
    }

    return $statTerms;
  }

  /**
   * Creates a stat paragraph entity.
   *
   * @param int $termId
   *   The stat term ID.
   * @param int $stat
   *   The base stat value.
   *
   * @return array
   *   The paragraph reference array.
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
