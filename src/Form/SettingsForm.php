<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for Pokemon API settings.
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
    $form['base_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Poke API URL'),
      '#description' => $this->t('The base URL for the PokeAPI, e.g. https://pokeapi.co/api/v2/'),
      '#default_value' => $this->config('pokemon_api.settings')->get('base_url'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('pokemon_api.settings')
      ->set('base_url', $form_state->getValue('base_url'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
