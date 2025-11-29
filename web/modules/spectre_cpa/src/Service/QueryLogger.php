<?php

namespace Drupal\spectre_cpa\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Log;

/**
 * Service logging and analyzing database queries
 */
class QueryLogger {
  /**
   * The database connection
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Query log data
   *
   * @var array
   */
  protected $queryLog = [];

  /**
   * Constructs query log object
   *
   * @param \Drupal\Core\Database\Connection $database
   *  The database connection
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Start query logging
   */
  public function startLogging() {
    if (!$this->database->getLogger()) {
      $this->database->setLogger(new Log());
    }
  }

  /**
   * Get logger queries
   *
   * @return array
   *  Array of query data
   */
  public function getQueries() {
    $logger = $this->database->getLogger();
    if (!$logger) {
      return [];
    }
    return  $logger->get('default') ?? [];
  }

  /**
   * Analyze Queries for the performance issues
   *
   * @param array $queries
   *  Array of queries to analyze
   *
   * @return array
   *  Analysis return with slow queries and recommendation
   */
  public function analyzeQueries(array $queries) {
    $analysis = [
      'total' => count($queries),
      'slow_queries' => [],
      'duplicate_queries' => [],
      'total_time' => 0,
    ];

    $query_hashes = [];
    foreach ($queries as $query) {
      $time = $query['time'] ?? 0;
      $analysis['total_time'] += $time;

      // Flag slow queries (>50ms)
      if ($time > 0.05) {
        $analysis['slow_queries'][] = [
          'query' => $query['query'] ?? '',
          'time' => $time * 1000,
          'caller' => $query['caller'] ?? 'unknown',
        ];
      }

      // Detect duplicate queries
      $hash = md5($query['query'] ?? '');
      if (!isset($query_hashes[$hash])) {
        $query_hashes[$hash]['count']++;
      } else {
        $query_hashes[$hash] = [
          'query' => $query['query'] ?? '',
          'count' => 1,
        ];
      }
    }

    // Find duplicates (executed more than once).
    foreach ($query_hashes as $data) {
      if ($data['count'] > 1) {
        $analysis['duplicate_queries'][] = [
          'query' => $data['query'],
          'count' => $data['count'],
        ];
      }
    }

    // sort slow queries by time
    usort($analysis['slow_queries'], function($a, $b) {
      return $b['time'] <=> $a['time'];
    });

    return $analysis;
  }
}
