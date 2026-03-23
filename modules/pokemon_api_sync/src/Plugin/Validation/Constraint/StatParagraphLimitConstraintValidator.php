<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Plugin\Validation\Constraint;

use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the StatParagraphLimit constraint.
 */
class StatParagraphLimitConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $items, Constraint $constraint): void {
    if (!$items instanceof FieldItemListInterface) {
      return;
    }

    $existingTerms = [];

    foreach ($items as $item) {
      $paragraph = $item->entity;
      if (!$paragraph) {
        continue;
      }

      $term = $paragraph->get('field_pokemon_stat')->entity;
      if (!$term) {
        continue;
      }

      if (in_array($term->id(), $existingTerms, TRUE)) {
        $this->context->addViolation($constraint->message, ['@term' => $term->label()]);
        break;
      }
      $existingTerms[] = $term->id();
    }
  }

}
