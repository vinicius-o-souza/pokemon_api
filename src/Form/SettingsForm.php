<?php

namespace Drupal\pokemon_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Pokemon API settings for this site.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'pokemon_api_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['pokemon_api.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['pokemon_api_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Pokemon API Url'),
      '#default_value' => $this->config('pokemon_api.settings')->get('pokemon_api_url'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('pokemon_api.settings')
      ->set('pokemon_api_url', $form_state->getValue('pokemon_api_url'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
