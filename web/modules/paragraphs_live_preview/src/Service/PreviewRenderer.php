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
     * Render preview HTML for a node with form data
     */
    public function renderPreview(EntityInterface $node, array $form_data): string {
        $this->applyFormData($node, $form_data);

        $view_builder = $this->entityTypeManager->getViewBuilder('node');
        $build = $view_builder->view($node, 'full');

        $wrapper = [
            '#type' => 'container',
            '#attributes' => [
                'class' => ['paragraphs-live-preview-wrapper'],
            ],
            'content' => $build,
        ];

        return $this->renderer->renderPlain($wrapper);
    }

    /**
     * Apply form data to node entity
     */
    protected function applyFormData(EntityInterface $node, array $form_data): void {
        if (isset($form_data['title'])) {
            $node->setTitle($form_data['title']);
        }

        foreach ($form_data as $field_name => $field_data) {
            if ($node->hasField($field_name) && is_array($field_data)) {
                $field_definition = $node->getFieldDefinition($field_name);
                if ($field_definition && $field_definition->getType() === 'entity_reference_revisions') {
                    $this->applyParagraphData($node, $field_name, $field_data);
                }
                else {
                  $node->set($field_name, $field_data);
                }
            }
        }
    }

    /*
     * Apply paragraph field data.
     */
  protected function applyParagraphData(EntityInterface $node, string $field_name, array $field_data): void {#
    $paragraph_items = [];

    foreach ($field_data as $delta => $item_data) {
      if (isset($item_data['target_id'])) {
        $paragraph = $this->entityTypeManager
          ->getStorage('paragraph')
          ->load($item_data['target_id']);

        if ($paragraph) {
          if (isset($item_data['fields'])) {
            foreach ($item_data['fields'] as $para_field_name => $para_field_value) {
              if ($paragraph->hasField($para_field_name)) {
                $paragraph->set($para_field_name, $para_field_value);
              }
            }
          }

          $paragraph_items[] = $paragraph;
        }
      }
    }

    if (!empty($paragraph_items)) {
      $node->set($field_name, $paragraph_items);
    }
  }
}
