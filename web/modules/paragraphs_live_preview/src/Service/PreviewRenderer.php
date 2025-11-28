<?php

namespace Drupal\paragraphs_live_preview\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Service for rendering preview of paragraphs.
 */
class PreviewRenderer {

  /**
   * The entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The renderer.
   */
  protected RendererInterface $renderer;

  /**
   * The theme manager.
   */
  protected ThemeManagerInterface $themeManager;

  /**
   * Constructs a PreviewRenderer object.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    RendererInterface $renderer,
    ThemeManagerInterface $theme_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->themeManager = $theme_manager;
  }

  /**
   * Render preview HTML for a node with form data.
   */
  public function renderPreview(EntityInterface $node, array $form_data): string {
    try {
      // Get the view builder
      $view_builder = $this->entityTypeManager->getViewBuilder('node');

      // Build the render array using 'full' view mode
      $build = $view_builder->view($node, 'full');

      // Add wrapper for styling
      $wrapper = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['paragraphs-live-preview-wrapper'],
        ],
        'content' => $build,
        '#attached' => [
          'library' => [
            'system/base',
          ],
        ],
      ];

      // Render and return HTML
      $html = $this->renderer->renderRoot($wrapper);

      // Wrap in a complete HTML document
      $full_html = '<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Preview</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      line-height: 1.6;
      padding: 20px;
      background: #fff;
    }
    .paragraphs-live-preview-wrapper {
      max-width: 100%;
    }
    h1, h2, h3, h4, h5, h6 {
      margin-top: 0;
      line-height: 1.2;
    }
  </style>
</head>
<body>
  ' . $html . '
</body>
</html>';

      return $full_html;
    }
    catch (\Exception $e) {
      \Drupal::logger('paragraphs_live_preview')->error('Render error: @message', [
        '@message' => $e->getMessage(),
      ]);

      return '<html><body><h1>Preview Error</h1><p>' . $e->getMessage() . '</p></body></html>';
    }
  }

}
