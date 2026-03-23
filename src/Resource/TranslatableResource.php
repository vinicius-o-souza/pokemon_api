<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Resource;

use Drupal\pokemon_api\Translation;

/**
 * Abstract base for resources with translatable names.
 */
abstract class TranslatableResource extends Resource implements TranslatableResourceInterface {

  /**
   * The translated names.
   */
  protected ?Translation $names = NULL;

  /**
   * {@inheritdoc}
   */
  public static function createFromArray(array $data): static {
    $resource = parent::createFromArray($data);
    $resource->setNames($data['names'] ?? []);

    return $resource;
  }

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
