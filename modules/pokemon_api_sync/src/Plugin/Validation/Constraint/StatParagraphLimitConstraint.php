<?php

namespace Drupal\pokemon_api_sync\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Custom validation constraint for limiting paragraphs by stat term.
 *
 * @Constraint(
 *   id = "StatParagraphLimit",
 *   label = @Translation("Stat paragraph limit by stat term", context = "Validation"),
 * )
 */
class StatParagraphLimitConstraint extends Constraint {
  public $message = 'Only one paragraph with the taxonomy term "@term" is allowed.';
}