<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Validates that each stat term appears only once in paragraph references.
 */
#[Constraint(
  id: 'StatParagraphLimit',
  label: new TranslatableMarkup('Stat paragraph limit by stat term', [], ['context' => 'Validation']),
)]
class StatParagraphLimitConstraint extends SymfonyConstraint {

  /**
   * The violation message.
   */
  public string $message = 'Only one paragraph with the taxonomy term "@term" is allowed.';

}
