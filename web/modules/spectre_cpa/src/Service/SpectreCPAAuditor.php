<?php

namespace Drupal\spectre_cpa\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Core service for Spectre Component Performance Auditor
 *
 * Tracks database queries, cache behavior, and component render
 * to provide visual performance attribution
 */
class SpectreCPAAuditor {
  /**
   * The database connection
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The render cache backend
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $renderCache;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The config factory
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Component tracking data.
   *
   * @var array
   */
  protected $componentData = [];

  /**
   * Current component stack.
   *
   * @var array
   */
  protected $componentStack = [];

  /**
   * Whether auditing is enabled.
   *
   * @var bool
   */
  protected $enabled = FALSE;

  /**
   * Constructs a SpectreCPAAuditor object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *  The database connection
   * @param \Drupal\Core\Cache\CacheBackendInterface $render_cache
   *  The render cache backend.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *  The current user
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *  The config factory
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *  The module handler
   */
  public function __construct(
    Connection $database,
    CacheBackendInterface $render_cache,
    AccountProxyInterface $current_user,
    ConfigFactoryInterface $config_factory,
    ModuleHandlerInterface $module_handler
  ) {
    $this->database = $database;
    $this->renderCache = $render_cache;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;

    // Enable auditing if user has permission
    $this->enabled = $current_user->hasPermission('administer cpa');
  }

  /**
   * Checks if auditing is enabled.
   *
   * @return bool
   *  TRUE if enabled, FALSE otherwise
   */
  public function isEnabled() {
    return $this->enabled;
  }

  /**
   * Starts tracking a component's performance
   *
   * @param string $component_id
   *  The unique component identifier
   * @param string $component_type
   *  The component type (block, view, sdc, etc.).
   * @param string $component_label
   *  Human-readable component label
   */
  public function startComponent(string $component_id, string $component_type, string $component_label) {
    if (!$this->enabled) { return; }
    $this->componentStack[] = $component_id;

    // Get current query count from logger if available.
    $start_query_count = 0;
    $logger = $this->database->getLogger();
    if ($logger) {
      $queries = $logger->get('default');
      $start_query_count = is_array($queries) ? count($queries) : 0;
    }

    $this->componentData[$component_id] = [
      'id' => $component_id,
      'type' => $component_type,
      'label' => $component_label,
      'start_time' => microtime(TRUE),
      'start_query_count' => $start_query_count,
      'queries' => [],
      'cache_status' => 'unknown',
      'cache_tags' => [],
      'cache_contexts' => [],
    ];
  }

  /**
   * Stops tracking a component and records final metrics
   *
   * @param string $component_id
   *  The component identifier
   * @param array $cache_metadata
   *  Cache metadata from the render array
   */
  public function stopComponent(string $component_id, array $cache_metadata) {
    if (!$this->enabled || !isset($this->componentData[$component_id])) { return; }

    $data = &$this->componentData[$component_id];
    $data['end_time'] = microtime(TRUE);
    $data['duration'] = ($data['end_time'] = $data['start_time']) * 1000; // Convert to ms
    $data['query_count'] = $this->database->queryCount() - $data['start_query_count'];

    // Process cache metadata
    if (!empty($cache_metadata)) {
      $data['cache_tags'] = $cache_metadata['tags'] ?? [];
      $data['cache_contexts'] = $cache_metadata['contexts'] ?? [];
      $data['cache_max_age'] = $cache_metadata['max-age'] ?? 0;

      //Determine cache status
      if (isset($cache_metadata['max-age']) && $cache_metadata['max-age'] === 0) {
        $data['cache_status'] = 'uncacheable';
      } elseif (!empty($cache_metadata['tags'])) {
        $data['cache_status'] = 'cacheable';
      }
    }

    // Remove from stack.
    $key = array_search($component_id, $this->componentStack);
    if ($key !== FALSE) {
      unset($this->componentStack[$key]);
    }
  }

  /**
   * Logs a database query for the current component
   *
   * @param string $query
   *  The query string
   * @param float $time
   *  Query execution time in seconds
   */
  public function logQuery(string $query, float $time) {
    if (!$this->enabled || empty($this->componentStack)) { return; }

    $current_component = end($this->componentStack);
    if (isset($this->componentData[$current_component])) {
      $this->componentData[$current_component]['queries'][] = [
        'query' => $query,
        'time' => $time * 1000,
      ];
    }
  }

  /**
   * Gets all component performance data.
   *
   * @return array
   *  Array of component data
   */
  public function getComponentData(): array {
    return $this->componentData;
  }

  /**
   * Gets performance data for a specific component
   *
   * @param string $component_id
   *  The component identifier
   *
   * @return array|null
   *  Component data or NULL if not found
   */
  public function getComponent(string $component_id): ?array {
    return $this->componentStack[$component_id] ?? NULL;
  }

  /**
   * Resets all tracking data.
   */
  public function reset() {
    $this->componentData = [];
    $this->componentStack = [];
  }

  /**
   * Generates a summary report of all components.
   *
   * @return array
   *  Summary statistics.
   */
  public function getSummary() {
    $summary = [
      'total_components' => count($this->componentData),
      'total_queries' => 0,
      'total_time' => 0,
      'uncacheable_count' => 0,
      'cacheable_count' => 0,
      'slowest_components' => [],
    ];

    foreach ($this->componentData as $component) {
      $summary['total_queries'] += $component['query_count'] ?? 0;
      $summary['total_time'] += $component['duration'] ?? 0;

      if (($component['cache_status'] ?? '') === 'uncacheable') {
        $summary['uncacheable_count']++;
      } elseif (($component['cache_status'] ?? '') === 'cacheable') {
        $summary['cacheable_count']++;
      }
    }

    // Find slowest components
    $sorted = $this->componentData;
    usort($sorted, function($a, $b) {
      return ($b['duration'] ?? 0) <=> ($a['duration'] ?? 0);
    });
    $summary['slowest_components'] = array_slice($sorted, 0 , 10);

    return $summary;
  }
}
