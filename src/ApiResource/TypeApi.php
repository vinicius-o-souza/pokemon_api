<?php

namespace Drupal\pokemon_api\ApiResource;

use Drupal\pokemon_api\Resource\Type;

/**
 * Class TypeApi to manage Types.
 */
class TypeApi extends ApiResource {

  /**
   * {@inheritdoc}
   *
   * @return class-string<Type>
   *   The resource model.
   */
  protected function getResourceModel(): string {
    return Type::class;
  }

}
