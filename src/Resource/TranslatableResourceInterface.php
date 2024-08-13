<?php

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Translation;

interface TranslatableResourceInterface {

  /**
   * Get the names.
   *
   * @return \Drupal\pokemon_api\Translation|null
   *   The names.
   */
  public function getNames(): ?Translation;

  /**
   * Set the names.
   *
   * @param array $names
   *   The names.
   */
  public function setNames(array $names);
  
}