<?php

namespace Drupal\spectre_cpa\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;

/**
 * Service for tracking cache hits, misses and invalidations
 */
class CacheTracker {
  /**
   * The render cache backend.
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Cache tracking data
   * @var array
   */
  protected $cacheData = [];

  /**
   * Constructs CacheTracker object.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $render_cache
   *  The render cache backend
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *  The cache tags invalidator
   */
  public function __construct(
    CacheBackendInterface $render_cache,
    CacheTagsInvalidatorInterface $cache_tags_invalidator
  ) {
    $this->renderCache = $render_cache;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * Tracks a cache operation
   *
   * @param string $component_id
   *  The component identifier
   * @param string $operation
   *  The cache operation (hit, miss, set)
   * @param array $tags
   *  Cache tags involved
   */
  public function track(string $component_id, string $operation, array $tags = []) {
    if (!isset($this->cacheData[$component_id])) {
      $this->cacheData[$component_id] = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'tags' => [],
      ];
    }
    $this->cacheData[$component_id][$operation . 's']++;
    $this->cacheData[$component_id]['tags'] = array_unique(array_merge($this->cacheData[$component_id]['tags'], $tags));
  }

  /**
   * Determine cache status for a component.
   *
   * @param array $render_array
   *  The render array to analyze
   *
   * @return string
   *  Cache status 'hit', 'miss' or 'uncacheable'
   */
  public function determineCacheStatus(array $render_array): string {
    //Check if component is cacheable
    if (isset($render_array['#cache']['max-age']) && $render_array['#cache']['max-age'] === 0) {
      return 'uncacheable';
    }

    //Check for cache metadata
    if (empty($render_array['#cache']['tags'])) {
      return 'uncacheable';
    }

    // if we have cache tags, assume cacheable (actual hit/miss determined at runtime)
    return 'cacheable';
  }

  /**
   * Gets cache data for the component
   *
   * @param string $component_id
   *  The component identifier
   *
   * @return array|null
   *  Cache data or NULL
   */
  public function getCacheData(string $component_id): ?array {
    return $this->cacheData[$component_id] ?? NULL;
  }

  /**
   * Get cache tracking data
   *
   * @return array
   *  All cache data
   */
  public function getAllCacheData(): array {
    return $this->cacheData;
  }

  /**
   * Reset cache tracking data
   */
  public function reset() {
    $this->cacheData = [];
  }

  /**
   * Analyze cache effectiveness
   *
   * @return array
   *  Cache analysis summary
   */
  public function analyzeCacheEffectiveness(): array {
    $total_hits = 0;
    $total_misses = 0;
    $uncacheable_components = [];

    foreach ($this->cacheData as $component_id => $data) {
      $total_hits += $data['hits'];
      $total_misses += $data['misses'];

      if ($data['hits'] === 0 && $data['misses'] > 0) {
        $uncacheable_components[] = $component_id;
      }
    }

    $total_requests = $total_hits + $total_misses;
    $hit_rate = $total_requests > 0 ? ($total_hits / $total_requests) * 100 : 0;

    return [
      'total_hits' => $total_hits,
      'total_misses' => $total_misses,
      'hit_rate' =>  round($hit_rate, 2),
      'uncacheable_components' => $uncacheable_components,
      'components_tracked' => count($this->cacheData),
    ];
  }
}
