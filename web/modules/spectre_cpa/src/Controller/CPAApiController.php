<?php

namespace Drupal\spectre_cpa\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\spectre_cpa\Service\SpectreCPAAuditor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for CPA API endpoints
 */
class CPAApiController extends ControllerBase {
  /**
   * The CPA auditor service.
   *
   * @var \Drupal\spectre_cpa\Service\SpectreCPAAuditor
   */
  protected $auditor;

  /**
   * Constructs a CPAApiController object.
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
   * Returns performance data for a specific component
   *
   * @param string $component_id
   *  The component identifier
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *  JSON response with component data.
   */
  public function componentData(string $component_id): JsonResponse {
    $data = $this->auditor->getComponent($component_id);

    if (!$data) {
      return new JsonResponse(
        ['error' => 'Component not found.'],
        404
      );
    }
    return new JsonResponse($data);
  }

  /**
   * Returns performance data for all components on the page.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *  JSON response with all component data.
   */
  public function pageData(): JsonResponse {
    $data = [
      'components' => $this->auditor->getComponentData(),
      'summary' => $this->auditor->getSummary(),
    ];
    return new JsonResponse($data);
  }
}
