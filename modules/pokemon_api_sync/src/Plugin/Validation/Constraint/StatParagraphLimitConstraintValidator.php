<?php

namespace Drupal\my_paragraph_limit\Plugin\Validation\Constraint;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class StatParagraphLimitConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!$items instanceof FieldItemListInterface) {
      return;
    }

    $existing_terms = [];

    foreach ($items as $delta => $item) {
      /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
      $paragraph = $item->entity;

      if ($paragraph instanceof EntityInterface) {
        $term = $paragraph->get('pokemon_stat')->entity;

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