<?php

namespace Drupal\pokemon_api_sync;

/**
 * Interface for ResourceInterface.
 */
interface ResourceInterface {

  /**
   * This function returns the entity type.
   *
   * @return string
   *   The entity type.
   */
  public function getEntityType(): string;

  /**
   * This function returns the entity bundle.
   *
   * @return string
   *   The entity bundle.
   */
  public function getBundle(): string;

}
