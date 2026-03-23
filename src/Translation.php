<?php

declare(strict_types=1);

namespace Drupal\pokemon_api;

/**
 * Handles field translations from PokeAPI responses.
 */
class Translation {

  /**
   * The language code for English.
   */
  public const string EN_LANGUAGE = 'en';

  /**
   * The language code for Spanish.
   */
  public const string ES_LANGUAGE = 'es';

  /**
   * The language code for Portuguese.
   */
  public const string PT_BR_LANGUAGE = 'pt-BR';

  /**
   * The parsed translations keyed by language code.
   *
   * @var array<string, string>
   */
  protected array $translations;

  /**
   * Constructs a Translation object.
   *
   * @param array $rawTranslations
   *   The raw translations from the API response.
   * @param string $position
   *   The key in each translation entry to extract.
   */
  public function __construct(array $rawTranslations, string $position = 'name') {
    $this->translations = [];
    $supportedLanguages = [self::EN_LANGUAGE, self::ES_LANGUAGE, self::PT_BR_LANGUAGE];

    foreach ($rawTranslations as $translation) {
      $languageCode = $translation['language']['name'];
      if (in_array($languageCode, $supportedLanguages, TRUE) && !isset($this->translations[$languageCode])) {
        $this->translations[$languageCode] = ucfirst(trim(str_replace("\n", '', $translation[$position])));
      }
    }
  }

  /**
   * Gets the translated value for a language.
   *
   * @param string $languageCode
   *   The language code.
   *
   * @return string|null
   *   The translated value, or NULL if not available.
   */
  public function getValue(string $languageCode): ?string {
    return $this->translations[$languageCode] ?? NULL;
  }

  /**
   * Gets all translations.
   *
   * @return array<string, string>
   *   The translations keyed by language code.
   */
  public function getTranslations(): array {
    return $this->translations;
  }

}
