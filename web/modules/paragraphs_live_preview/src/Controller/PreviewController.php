<?php

namespace Drupal\paragraphs_live_preview\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\paragraphs_live_preview\Service\PreviewRenderer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for handling live preview requests.
 */
class PreviewController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The preview renderer service.
   *
   * @var \Drupal\paragraphs_live_preview\Service\PreviewRenderer
   */
  protected $previewRenderer;

  /**
   * Constructs a PreviewController object.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    PreviewRenderer $preview_renderer
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->previewRenderer = $preview_renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('paragraphs_live_preview.renderer')
    );
  }

  /**
   * Generate preview for paragraphs.
   */
  public function preview(Request $request): JsonResponse {
    try {
      $node_id = $request->request->get('node_id');
      $serialized_form = $request->request->get('serialized_form');
      $node_type = $request->request->get('node_type');

      if (empty($serialized_form)) {
        return new JsonResponse([
          'success' => FALSE,
          'error' => 'No form data provided',
        ], 400);
      }

      // Parse the serialized form data
      parse_str($serialized_form, $form_data);

      // Log for debugging
      \Drupal::logger('paragraphs_live_preview')->notice('Node ID: @id', ['@id' => $node_id ?? 'none']);
      \Drupal::logger('paragraphs_live_preview')->notice('Node Type: @type', ['@type' => $node_type ?? 'none']);

      // Clean up node_type (remove -edit suffix if present)
      $clean_node_type = str_replace('-edit', '', $node_type ?? 'article');

      // Load or create node
      if (!empty($node_id) && is_numeric($node_id)) {
        $node = $this->entityTypeManager->getStorage('node')->load($node_id);
        if (!$node) {
          throw new \Exception("Node with ID {$node_id} not found");
        }
        \Drupal::logger('paragraphs_live_preview')->notice('Loaded existing node: @id', ['@id' => $node_id]);
      }
      else {
        // Create temporary node for preview
        $node = $this->entityTypeManager->getStorage('node')->create([
          'type' => $clean_node_type,
          'title' => 'Preview',
          'status' => 1,
        ]);
        \Drupal::logger('paragraphs_live_preview')->notice('Created temporary node of type: @type', ['@type' => $clean_node_type]);
      }

      // Apply form data to node
      $this->applyFormDataToNode($node, $form_data);

      // Render the preview
      $rendered_html = $this->previewRenderer->renderPreview($node, $form_data);

      return new JsonResponse([
        'success' => TRUE,
        'html' => $rendered_html,
      ]);
    }
    catch (\Exception $e) {
      \Drupal::logger('paragraphs_live_preview')->error('Preview error: @message', [
        '@message' => $e->getMessage(),
      ]);
      \Drupal::logger('paragraphs_live_preview')->error('Stack trace: @trace', [
        '@trace' => $e->getTraceAsString(),
      ]);

      return new JsonResponse([
        'success' => FALSE,
        'error' => $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Apply form data to node entity.
   */
  protected function applyFormDataToNode($node, array $form_data): void {
    // Set title
    if (isset($form_data['title'][0]['value']) && !empty($form_data['title'][0]['value'])) {
      $node->setTitle($form_data['title'][0]['value']);
      \Drupal::logger('paragraphs_live_preview')->notice('Set title: @title', [
        '@title' => $form_data['title'][0]['value'],
      ]);
    }

    // Set body
    if (isset($form_data['body'][0]['value']) && !empty($form_data['body'][0]['value'])) {
      $node->set('body', [
        'value' => $form_data['body'][0]['value'],
        'format' => $form_data['body'][0]['format'] ?? 'basic_html',
      ]);
    }

    // Handle other simple fields
    $simple_fields = ['status', 'promote', 'sticky'];
    foreach ($simple_fields as $field_name) {
      if (isset($form_data[$field_name]['value'])) {
        $node->set($field_name, $form_data[$field_name]['value']);
      }
    }
  }

}
