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
  }
}
