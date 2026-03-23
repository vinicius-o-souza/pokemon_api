<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Translation;

/**
 * Interface for resources that support translations.
 */
interface TranslatableResourceInterface {

  /**
   * Gets the translated names.
   *
   * @return \Drupal\pokemon_api\Translation|null
   *   The names, or NULL if not set.
   */
  public function getNames(): ?Translation;

  /**
   * Sets the translated names.
   *
   * @param array $names
   *   The raw names data from the API.
   */
  public function setNames(array $names): void;

}
