<?php

namespace Drupal\paragraphs_live_preview\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\node\Entity\NodeType;

/**
 * Configure Paragraphs Live Preview settings
 */
class SettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['paragraphs_live_preview.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'paragraphs_live_preview_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('paragraphs_live_preview.settings');

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable paragraphs live'),
      '#description' => $this->t('Enable or disable the live preview feature globally.'),
      '#default_value' => $config->get('enabled') ?? TRUE,
    ];

    $form['debounce_delay'] = [
      '#type' => 'number',
      '#title' => $this->t('Debounce delay (ms)'),
      '#description' => $this->t('Delay in milliseconds before updating preview.'),
      '#default_value' => $config->get('debounce_delay') ?? 500,
      '#min' => 100,
      '#max' => 2000,
      '#step' => 100,
    ];

    $content_types = NodeType::loadMultiple();
    $options = [];
    foreach ($content_types as $type) {
      $options[$type->id()] = $type->label();
    }

    $form['enabled_content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enable content types'),
      '#description' => $this->t('Select content types for live preview.'),
      '#options' => $options,
      '#default_value' => $config->get('enabled_content_types') ?? [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('paragraphs_live_preview.settings')
      ->set('enabled', $form_state->getValue('enabled'))
      ->set('debounce_delay', $form_state->getValue('debounce_delay'))
      ->set('enabled_content_types', array_filter($form_state->getValue('enabled_content_types')))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
