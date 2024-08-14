<?php

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Translation;

/**
 * Resource TranslatableResource class.
 */
abstract class TranslatableResource extends Resource implements TranslatableResourceInterface {

  /**
   * The names.
   *
   * @var \Drupal\pokemon_api\Translation|null
   */
  protected Translation|null $names;

  /**
   * {@inheritdoc}
   */
  public function getNames(): ?Translation {
    return $this->names;
  }

  /**
   * {@inheritdoc}
   */
  public function setNames(array $names): void {
    $this->names = new Translation($names);
  }

}
