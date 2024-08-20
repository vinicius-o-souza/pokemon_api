<?php

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Translation;

/**
 * Trait for FlavorText.
 */
trait FlavorTextTrait {

  /**
   * The flavor text listed in different languages.
   *
   * @var \Drupal\pokemon_api\Translation
   *   The flavor text listed in different languages.
   */
  protected Translation $flavorText;

  /**
   * Get the flavor text listed in different languages.
   *
   * @return \Drupal\pokemon_api\Translation
   *   The flavor text listed in different languages.
   */
  public function getFlavorText(): Translation {
    return $this->flavorText;
  }

  /**
   * Set the flavor text listed in different languages.
   *
   * @param array $flavorText
   *   The flavor text listed in different languages.
   */
  public function setFlavorText($flavorText): void {
    $this->flavorText = new Translation($flavorText, 'flavor_text');
  }

}
