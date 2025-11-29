<?php

namespace Drupal\spectre_cpa\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Component Performance Auditor settings
 */
class SpectreCPASettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'spectre_cpa_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return['spectre_cpa.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('spectre_cpa.settings');

    $form['enable_overlay'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable visual overlay'),
      '#description' => $this->t('Show performance data overlay on pages when you have the "administer spectre cpa" permission.'),
      '#default_value' => $config->get('enable_overlay') ?? TRUE,
    ];

    $form['enable_query_logging'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable detailed query logging'),
      '#description' => $this->t('Log individual queries executed by each component. <strong>Warning:</strong> This can impact performance on high-traffic site.'),
      '#default_value' => $config->get('enable_query_logging') ?? FALSE,
    ];

    $form['slow_query_threshold'] = [
      '#type' => 'number',
      '#title' => $this->t('Enable derailed query logging'),
      '#description' => $this->t('Queries slower than this threshold will be highlighted.'),
      '#default_value' => $config->get('slow_query_threshold') ?? 50,
      '#min' => 1,
      '#step' => 1,
    ];

    $form['cache_analysis'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable cache analysis'),
      '#description' => $this->t('Track cache hits, misses, and effectiveness.'),
      '#default_value' => $config->get('cache_analysis') ?? TRUE,
    ];

    $form['component_types'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable cache analysis'),
      '#description' =>  $this->t('Select which component types should be tracked.'),
      '#options' => [
        'block' => $this->t('Block'),
        'view' => $this->t('Views'),
        'sdc' => $this->t('Single Directory Components'),
        'field' => $this->t('Fields'),
        'paragraph' => $this->t('Paragraph'),
      ],
      '#default_value' => $config->get('component_types') ?? ['block', 'view', 'sdc'],
    ];

    $form['performance'] = [
      '#type' => 'details',
      '#title' => $this->t('Performance Settings'),
      '#open' => FALSE,
    ];

    $form['performance']['max_components'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum components to track'),
      '#description' => $this->t('Limit the number of components percentage of page loads. 100 = always track.'),
      '#default_value' => $config->get('sample_rate') ?? 100,
      '#min' => 1,
      '#max' => 100,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('spectre_cpa.settings')
      ->set('enable_overlay', $form_state->getValue('enable_overlay'))
      ->set('enable_query_logging', $form_state->getValue('slow_query_threshold'))
      ->set('slow_query_threshold', $form_state->getValue('slow_query_threshold'))
      ->set('cache_analysis', $form_state->getValue('cache_analysis'))
      ->set('component_types', array_filter($form_state->getValues('component_type')))
      ->set('max_components', $form_state->getValue('max_components'))
      ->set('sample_rate', $form_state->getValue('sample_rate'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
