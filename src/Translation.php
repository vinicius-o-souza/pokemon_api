<?php

namespace Drupal\pokemon_api;

/**
 * Translation class to handle fields translations.
 */
class Translation {

  /**
   * The language code for English.
   *
   * @var string
   */
  public const EN_LANGUAGE = 'en';

  /**
   * The language code for Espanish.
   *
   * @var string
   */
  public const ES_LANGUAGE = 'es';

  /**
   * The language code for Portuguese.
   *
   * @var string
   */
  public const PT_BR_LANGUAGE = 'pt-BR';

  /**
   * Constructor for the Translation class.
   *
   * @param array $translations
   *   The translations to be stored.
   * @param string $position
   *   The position of the translation.
   */
  public function __construct(protected array $translations, string $position = 'name') {
    $this->translations = [];

    foreach ($translations as $translation) {
      $languageCode = $translation['language']['name'];
      if (in_array($languageCode, [self::EN_LANGUAGE, self::ES_LANGUAGE, self::PT_BR_LANGUAGE])) {
        if (!isset($this->translations[$languageCode])) {
          $this->translations[$languageCode] = ucfirst(trim($translation[$position])); 
        }
      }
    }
  }

  /**
   * Get the value.
   *
   * @param string $languageCode
   *   The language code.
   *
   * @return string|null
   *   The value.
   */
  public function getValue(string $languageCode): ?string {
    if (empty($this->translations[$languageCode])) {
      return NULL;
    }

    return $this->translations[$languageCode];
  }

  /**
   * Get the translations.
   *
   * @return array
   *   The translations.
   */
  public function getTranslations(): array {
    return $this->translations;
  }

}
