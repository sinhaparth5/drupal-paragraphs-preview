<?php

namespace Drupal\spectre_cpa\EventSubscriber;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\spectre_cpa\Service\SpectreCPAAuditor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber for tracking component rendering
 */
class RenderSubscriber implements EventSubscriberInterface {
  /**
   * The Spectre CPA auditor service
   *
   * @var \Drupal\spectre_cpa\Service\SpectreCPAAuditor
   */
  protected $auditor;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a RenderSubscriber object.
   *
   * @param \Drupal\spectre_cpa\Service\SpectreCPAAuditor $auditor
   *  The CPA auditor service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *  The current user.
   */
  public function __construct(SpectreCPAAuditor $auditor, AccountProxyInterface $current_user) {
    $this->auditor = $auditor;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onResponse', -100];
    return $events;
  }

  /**
   * Responds to the kernel response event.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *  The even object
   */
  public function onResponse(ResponseEvent $event) {
    // Only process if auditor is enabled.
    if (!$this->auditor->isEnabled()) { return; }

    // Only process HTML responses
    $response = $event->getResponse();
    $content_type = $response->headers->get('Content-Type');
    if (!$content_type || strpos($content_type, 'text/html') === FALSE) {
      return;
    }

    // Get component data ad inject into page
    $component_data = $this->auditor->getComponentData();
    if (empty($component_data)) {
      return;
    }

    // Inject CPA data into the response.
    $content = $response->getContent();
    $cpa_data = [
      'components' => $component_data,
      'summary' => $this->auditor->getSummary(),
    ];

    // Add inline script with CPA data.
    $script = sprintf(
      '<script>window.spectre_cpaData = %s;</script>',
      json_encode($cpa_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)
    );
    // Inject before closing body tag.
    if (strpos($content, '</body>') !== FALSE) {
      $content = str_replace('</body>', $script . '</body>', $content);
      $response->setContent($content);
    }
  }
}
