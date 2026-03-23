<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Translation;

/**
 * Provides flavor text handling for resources.
 */
trait FlavorTextTrait {

  /**
   * The flavor text in different languages.
   */
  protected Translation $flavorText;

  /**
   * Gets the flavor text.
   *
   * @return \Drupal\pokemon_api\Translation
   *   The flavor text translations.
   */
  public function getFlavorText(): Translation {
    return $this->flavorText;
  }

  /**
   * Sets the flavor text from raw API data.
   *
   * @param array $flavorText
   *   The raw flavor text entries.
   */
  public function setFlavorText(array $flavorText): void {
    $this->flavorText = new Translation($flavorText, 'flavor_text');
  }

}
