<?php

namespace Drupal\pokemon_api;

/**
 * Translation class to handle fields translations.
 */
class Translation {

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
   */
  public function __construct(protected array $translations) {
    $this->translations = [];

    foreach ($translations as $translation) {
      $this->translations[$translation['language']['name']] = $translation['name'];
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
