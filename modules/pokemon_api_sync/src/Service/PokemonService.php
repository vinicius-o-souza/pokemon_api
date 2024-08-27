<?php

namespace Drupal\pokemon_api_sync\Service;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\pokemon_api\Resource\Pokemon;

/**
 * Service to manage pokemon.
 */
class PokemonService {

  /**
   * PokemonService constructor.
   *
   * @param \Drupal\pokemon_api_sync\Service\MoveParagraphService $moveParagraphService
   *   The move paragraph service.
   * @param \Drupal\pokemon_api_sync\Service\StatParagraphService $statParagraphService
   *   The stat paragraph service.
   */
  public function __construct(
    private readonly MoveParagraphService $moveParagraphService,
    private readonly StatParagraphService $statParagraphService,
  ) {}

  /**
   * Get pokemon stats.
   *
   * @param \Drupal\pokemon_api\Resource\Pokemon $pokemon
   *   The pokemon.
   * @param \Drupal\Core\Entity\ContentEntityBase|null $node
   *   The pokemon node.
   *
   * @return array
   *   The stats.
   */
  public function getParagraphs(Pokemon $pokemon, ?ContentEntityBase $node): array {
    return [
      // 'moves' => $this->moveParagraphService->getOrCreateMoveParagraphs($pokemon->getMoves(), $node),
      'stats' => $this->statParagraphService->getOrCreateStatParagraphs($pokemon->getStats(), $node),
    ];
  }

}
