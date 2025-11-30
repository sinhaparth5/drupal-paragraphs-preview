<?php

namespace Drupal\spectre_cpa\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
* Configure Component Performance Auditor settings.
*/
class SpectreCPASettingsForm extends ConfigFormBase {
  /**
  * {@inheritdoc}
  */
  public function getFormId() {
    return 'cpa_settings_form';
  }

  /**
  * {@inheritdoc}
  */
  protected function getEditableConfigNames() {
    return ['cpa.settings'];
  }

  /**
  * {@inheritdoc}
  */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cpa.settings');

    $form['enable_overlay'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable visual overlay'),
      '#description' => $this->t('Show performance data overlay on pages when you have the "administer cpa" permission.'),
      '#default_value' => $config->get('enable_overlay') ?? TRUE,
    ];

    $form['enable_query_logging'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable detailed query logging'),
      '#description' => $this->t('Log individual queries executed by each component. <strong>Warning:</strong> This can impact performance on high-traffic sites.'),
      '#default_value' => $config->get('enable_query_logging') ?? FALSE,
    ];

    $form['slow_query_threshold'] = [
      '#type' => 'number',
      '#title' => $this->t('Slow query threshold (ms)'),
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

    $component_types = $config->get('component_types');
    // Convert array to associative array for checkboxes.
    // Checkboxes need ['key' => 'key'] format for checked items.
    if (is_array($component_types)) {
      $component_types = array_combine($component_types, $component_types);
    }
    else {
      $component_types = ['block' => 'block', 'view' => 'view', 'sdc' => 'sdc'];
    }

    $form['component_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Component types to track'),
      '#description' => $this->t('Select which component types should be tracked.'),
      '#options' => [
        'block' => $this->t('Blocks'),
        'view' => $this->t('Views'),
        'sdc' => $this->t('Single Directory Components'),
        'field' => $this->t('Fields'),
        'paragraph' => $this->t('Paragraphs'),
      ],
      '#default_value' => $component_types,
    ];

    $form['performance'] = [
      '#type' => 'details',
      '#title' => $this->t('Performance Settings'),
      '#open' => FALSE,
    ];

    $form['performance']['max_components'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum components to track'),
      '#description' => $this->t('Limit the number of components tracked per page to reduce overhead.'),
      '#default_value' => $config->get('max_components') ?? 100,
      '#min' => 10,
      '#max' => 500,
    ];

    $form['performance']['sample_rate'] = [
      '#type' => 'number',
      '#title' => $this->t('Sampling rate (%)'),
      '#description' => $this->t('Only track performance on a percentage of page loads. 100 = always track.'),
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
    $this->config('cpa.settings')
    ->set('enable_overlay', $form_state->getValue('enable_overlay'))
    ->set('enable_query_logging', $form_state->getValue('enable_query_logging'))
    ->set('slow_query_threshold', $form_state->getValue('slow_query_threshold'))
    ->set('cache_analysis', $form_state->getValue('cache_analysis'))
    ->set('component_types', array_filter($form_state->getValue('component_types')))
    ->set('max_components', $form_state->getValue('max_components'))
    ->set('sample_rate', $form_state->getValue('sample_rate'))
    ->save();

    parent::submitForm($form, $form_state);
  }
}
