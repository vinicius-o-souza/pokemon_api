<?php

namespace Drupal\pokemon_api_sync\Plugin\Validation\Constraint;

use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Custom validation constraint for limiting paragraphs by stat term.
 */
class StatParagraphLimitConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $items, Constraint $constraint): void {
    if (!$items instanceof FieldItemListInterface) {
      return;
    }

    $existing_terms = [];

    foreach ($items as $delta => $item) {
      $paragraph = $item->entity;

      if ($paragraph) {
        $term = $paragraph->get('field_pokemon_stat')->entity;

        if ($term) {
          if (in_array($term->id(), $existing_terms)) {
            $this->context->addViolation($constraint->message, ['@term' => $term->label()]);
            break;
          }
          $existing_terms[] = $term->id();
        }
      }
    }
  }

}
