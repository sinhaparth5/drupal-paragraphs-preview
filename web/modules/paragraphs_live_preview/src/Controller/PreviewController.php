<?php

namespace Drupal\paragraphs_live_preview\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\paragraphs_live_preview\Service\PreviewRenderer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for handling live preview requests
 */
class PreviewController extends ControllerBase {
  /**
   * The preview renderer service
   *
   * @var \Drupal\paragraphs_live_preview\Service\PreviewRenderer
   */
  protected $previewRenderer;

  /**
   * Constructs a PreviewController object
   */
  public function __construct(
    PreviewRenderer $preview_renderer
  ) {
    $this->previewRenderer = $preview_renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('paragraphs_live_preview.renderer')
    );
  }

  /**
   * Generate preview for paragraphs
   */
  public function preview(Request $request): JsonResponse {
    $node_id = $request->request->get('node_id');
    $form_data = $request->request->get('form_data');

    if (empty($form_data)) {
      return new JsonResponse([
        'success' => FALSE,
        'error' => 'No form data provided',
      ], 400);
    }

    try {
      if ($node_id) {
        $node = $this->entityTypeManager->getStorage('node')->load($node_id);
        if (!$node) {
          throw new \Exception('Node not found');
        }
      }
      else {
        $node_type = $form_data['type'] ?? 'article';
        $node = $this->entityTypeManager->getStorage('node')->create([
          'type' => $node_type,
          'title' => $form_data['title'] ?? 'Preview',
        ]);
      }

      $rendered_html = $this->previewRenderer->renderPreview($node, (array) $form_data);

      return new JsonResponse([
        'success' => TRUE,
        'html' => $rendered_html,
      ]);
    }
    catch (\Exception $e) {
      $this->getLogger('paragraphs_live_preview')->error($e->getMessage());

      return new JsonResponse([
        'success' => FALSE,
        'error' => $e->getMessage(),
      ], 500);
    }
  }
}
