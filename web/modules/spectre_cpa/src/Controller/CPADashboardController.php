<?php

namespace Drupal\spectre_cpa\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\spectre_cpa\Service\SpectreCPAAuditor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for CPA dashboard
 */
class CPADashboardController extends ControllerBase {
  /**
   * The CPA auditor service.
   *
   * @var \Drupal\spectre_cpa\Service\SpectreCPAAuditor
   */
  protected $auditor;

  /**
   * Constructs a Spectre CPADashboardController object
   *
   * @param \Drupal\spectre_cpa\Service\SpectreCPAAuditor $auditor
   *  The CPA auditor service.
   */
  public function __construct(SpectreCPAAuditor $auditor) {
    $this->auditor = $auditor;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('spectre_cpa.auditor')
    );
  }

  /**
   * Displays the performance dashboard
   *
   * @return array
   *  Render array for the dashboard
   */
  public function dashboard() {
    $summary = $this->auditor->getSummary();
    $components = $this->auditor->getComponentData();

    $build = [
      '#theme' => 'spectre_cpa_dashboard',
      '#summary' => $summary,
      '#components' => $components,
      '#attached' => [
        'library' => ['spectre_cpa/dashboard'],
      ],
    ];

    // Add summary statistics.
    $build['summary'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['spectre-cpa-summary', 'container-inline']],
      'title' => [
        '#markup' => '<h2>' . $this->t('Performance Summary') . '</h2>',
      ],
      'stats' => [
        '#theme' => 'item_list',
        '#items' => [
          $this->t('Total Components: @count', ['@count' => $summary['total_components']]),
          $this->t('Total Queries: @count', ['@count' => $summary['total_queries']]),
          $this->t('Total Time: @time ms', ['@time' => round($summary['total_time'], 2)]),
          $this->t('Cacheable: @count', ['@count' => $summary['cacheable_count']]),
          $this->t('Uncacheable: @count', ['@count' => $summary['uncacheable_count']]),
        ],
      ],
    ];

    // Add slowest components table.
    if (!empty($summary['slowest_components'])) {
      $rows = [];
      foreach ($summary['slowest_components'] as $component) {
        $rows[] = [
          $component['label'] ?? $component['id'],
          $component['type'] ?? 'unknown',
          round($component['duration'] ?? 0, 2) . ' ms',
          $component['query_count'] ?? 0,
          $component['cache_status'] ?? 'unknown',
        ];
      }

      $build['slowest'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['spectre-cpa-slowest', 'container-inline']],
        'title' => [
          '#markup' => '<h2>' . $this->t('Slowest Components') . '</h2>',
        ],
        'table' => [
          '#type' => 'table',
          '#header' => [
            $this->t('Component'),
            $this->t('Type'),
            $this->t('Duration'),
            $this->t('Queries'),
            $this->t('Cache Status'),
          ],
          '#rows' => $rows,
        ],
      ];
    }
    return $build;
  }
}
