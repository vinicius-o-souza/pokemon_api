<?php

namespace Drupal\pokemon_api_sync\Service;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Service to manage move paragraphs.
 */
class MoveParagraphService {

  /**
   * List of move terms.
   *
   * @var array
   */
  private array $moveTerms = [];

  /**
   * Constructs a new MoveParagraphService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(private readonly EntityTypeManager $entityTypeManager) {
    $this->moveTerms = $this->getMoveTerms();
  }

  /**
   * Get or create pokemon moves.
   *
   * @param array $pokemonMoves
   *   The moves terms.
   * @param \Drupal\Core\Entity\ContentEntityBase|null $node
   *   The pokemon node.
   *
   * @return array
   *   The moves.
   */
  public function getOrCreateMoveParagraphs(array $pokemonMoves, ?ContentEntityBase $node): array {
    $moves = [];
    if ($node) {
      /** @var \Drupal\paragraphs\Entity\Paragraph[] $paragraphs */
      $paragraphs = $node->get('field_pokemon_moves')->referencedEntities();
      foreach ($paragraphs as $paragraph) {
        $moveTerm = $paragraph->get('field_pokemon_move')->entity;
        if ($moveTerm) {
          $movePokeApiId = $moveTerm->get('field_pokeapi_id')->value;
          if ($movePokeApiId) {
            $paragraph->set('field_pokemon_base_move', $pokemonMoves[$movePokeApiId]);
            $paragraph->save();

            $moves[] = [
              'target_id' => $paragraph->id(),
              'target_revision_id' => $paragraph->getRevisionId(),
            ];
            unset($pokemonMoves[$movePokeApiId]);
          }
        }
      }
    }

    foreach ($pokemonMoves as $key => $move) {
      if ($this->moveTerms['pokemon_move'][$key]) {
        $moves[] = $this->createMoveParagraph($this->moveTerms['pokemon_move'][$key], $move);
      }
    }

    return $moves;
  }

  /**
   * Retrieves the list of move terms from the taxonomy storage.
   *
   * @return array
   *   Array of moves where the key is the PokeAPI ID and the value term ID.
   */
  private function getMoveTerms(): array {
    /** @var \Drupal\taxonomy\Entity\Term[] $terms */
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'pokemon_move',
    ]);

    $moveTerms = [];
    foreach ($terms as $term) {
      $moveTerms[$term->get('field_pokeapi_id')->getString()] = $term->id();
    }

    return $moveTerms;
  }

  /**
   * Create move paragraph.
   *
   * @param int $termId
   *   The term id.
   * @param int $move
   *   The move.
   *
   * @return array
   *   The move paragraph.
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
