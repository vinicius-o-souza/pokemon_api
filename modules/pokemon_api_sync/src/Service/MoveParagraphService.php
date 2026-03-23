<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Service;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Manages move paragraph entities.
 */
class MoveParagraphService {

  /**
   * Cached move terms keyed by PokeAPI ID.
   *
   * @var array<string, int>
   */
  private array $moveTerms = [];

  /**
   * Constructs a MoveParagraphService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(private readonly EntityTypeManagerInterface $entityTypeManager) {
    $this->moveTerms = $this->loadMoveTerms();
  }

  /**
   * Gets or creates move paragraphs for a Pokémon.
   *
   * @param array $pokemonMoves
   *   Moves keyed by PokeAPI ID.
   * @param \Drupal\Core\Entity\ContentEntityBase|null $node
   *   The existing Pokémon node, or NULL.
   *
   * @return array
   *   Paragraph reference arrays.
   */
  public function getOrCreateMoveParagraphs(array $pokemonMoves, ?ContentEntityBase $node): array {
    $moves = [];

    if ($node) {
      /** @var \Drupal\paragraphs\Entity\Paragraph[] $paragraphs */
      $paragraphs = $node->get('field_pokemon_moves')->referencedEntities();
      foreach ($paragraphs as $paragraph) {
        $moveTerm = $paragraph->get('field_pokemon_move')->entity;
        if (!$moveTerm) {
          continue;
        }

        $movePokeApiId = $moveTerm->get('field_pokeapi_id')->value;
        if (!$movePokeApiId || !isset($pokemonMoves[$movePokeApiId])) {
          continue;
        }

        $paragraph->set('field_pokemon_base_move', $pokemonMoves[$movePokeApiId]);
        $paragraph->save();

        $moves[] = [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ];
        unset($pokemonMoves[$movePokeApiId]);
      }
    }

    foreach ($pokemonMoves as $key => $move) {
      if (isset($this->moveTerms[$key])) {
        $moves[] = $this->createMoveParagraph($this->moveTerms[$key], $move);
      }
    }

    return $moves;
  }

  /**
   * Loads move terms from taxonomy storage.
   *
   * @return array<string, int>
   *   Term IDs keyed by PokeAPI ID.
   */
  private function loadMoveTerms(): array {
    /** @var \Drupal\taxonomy\Entity\Term[] $terms */
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'pokemon_move',
    ]);

    $moveTerms = [];
    foreach ($terms as $term) {
      $moveTerms[$term->get('field_pokeapi_id')->getString()] = (int) $term->id();
    }

    return $moveTerms;
  }

  /**
   * Creates a move paragraph entity.
   *
   * @param int $termId
   *   The move term ID.
   * @param int $move
   *   The base move value.
   *
   * @return array
   *   The paragraph reference array.
   */
  private function createMoveParagraph(int $termId, int $move): array {
    $paragraph = $this->entityTypeManager->getStorage('paragraph')->create([
      'type' => 'pokemon_moves',
      'field_pokemon_move' => $termId,
      'field_pokemon_base_move' => $move,
    ]);
    $paragraph->save();

    return [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
  }

}
