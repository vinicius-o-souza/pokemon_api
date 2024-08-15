<?php

namespace Drupal\pokemon_api\ApiResource;

use Drupal\pokemon_api\ApiResource;
use Drupal\pokemon_api\Resource\Ability;

/**
 * Class AbilityApi to manage abilities.
 */
class AbilityApi extends ApiResource {

  /**
   * {@inheritdoc}
   *
   * @return class-string<Ability>
   *   The resource model.
   */
  protected function getResourceModel(): string {
    return Ability::class;
  }

}
