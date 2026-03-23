<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Service;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\pokemon_api\Resource\Pokemon;

/**
 * Manages Pokémon paragraph data.
 */
class PokemonService {

  /**
   * Constructs a PokemonService object.
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
   * Gets paragraph references for a Pokémon.
   *
   * @param \Drupal\pokemon_api\Resource\Pokemon $pokemon
   *   The Pokémon resource.
   * @param \Drupal\Core\Entity\ContentEntityBase|null $node
   *   The existing node, or NULL.
   *
   * @return array
   *   Paragraph references keyed by type ('moves', 'stats').
   */
  public function getParagraphs(Pokemon $pokemon, ?ContentEntityBase $node): array {
    return [
      // 'moves' => $this->moveParagraphService->getOrCreateMoveParagraphs($pokemon->getMoves(), $node),
      'stats' => $this->statParagraphService->getOrCreateStatParagraphs($pokemon->getStats(), $node),
    ];
  }

}
