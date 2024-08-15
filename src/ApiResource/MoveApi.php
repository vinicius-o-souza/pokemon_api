<?php

namespace Drupal\pokemon_api\ApiResource;

use Drupal\pokemon_api\ApiResource;
use Drupal\pokemon_api\Resource\Move;

/**
 * Class MoveApi to manage Moves.
 */
class MoveApi extends ApiResource {

  /**
   * {@inheritdoc}
   *
   * @return class-string<Move>
   *   The resource model.
   */
  protected function getResourceModel(): string {
    return Move::class;
  }

}
