<?php

namespace Drupal\pokemon_api\ApiResource;

use Drupal\pokemon_api\Resource\Stat;

/**
 * Class StatApi to manage Stats.
 */
class StatApi extends ApiResource {

  /**
   * {@inheritdoc}
   *
   * @return class-string<Stat>
   *   The resource model.
   */
  protected function getResourceModel(): string {
    return Stat::class;
  }

}
