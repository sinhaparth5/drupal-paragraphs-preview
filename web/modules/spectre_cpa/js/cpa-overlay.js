/**
 * @file
 * JavaScript for CPA visual overlay.
 */

(function ($, Drupal, drupalSettings, once) {
  'use strict';

  Drupal.behaviors.cpaOverlay = {
    attach: function (context, settings) {
      // Only run once.
      if (context !== document) {
        return;
      }

      // Check if CPA data is available.
      if (!window.cpaData || !window.cpaData.components) {
        console.log('CPA: No component data available');
        return;
      }

      const components = window.cpaData.components;
      const summary = window.cpaData.summary;

      // Create toolbar.
      this.createToolbar(summary);

      // Wrap components and add hover effects.
      this.wrapComponents(components);

      console.log('CPA: Overlay initialized with ' + Object.keys(components).length + ' components');
    },

    createToolbar: function (summary) {
      const toolbar = $('<div class="cpa-toolbar"></div>');

      toolbar.html(`
        <div class="cpa-toolbar-title">⚡ Performance Monitor</div>
        <div class="cpa-toolbar-stat">
          <span>Components:</span>
          <strong>${summary.total_components || 0}</strong>
        </div>
        <div class="cpa-toolbar-stat">
          <span>Queries:</span>
          <strong>${summary.total_queries || 0}</strong>
        </div>
        <div class="cpa-toolbar-stat">
          <span>Time:</span>
          <strong>${(summary.total_time || 0).toFixed(2)} ms</strong>
        </div>
        <div class="cpa-toolbar-stat">
          <span>Cacheable:</span>
          <strong>${summary.cacheable_count || 0}</strong>
        </div>
        <div class="cpa-toolbar-stat">
          <span>Uncacheable:</span>
          <strong class="cpa-metric-slow">${summary.uncacheable_count || 0}</strong>
        </div>
        <button class="cpa-toolbar-toggle" data-toggle="overlay">Toggle Overlay</button>
      `);

      $('body').append(toolbar);

      // Toggle overlay visibility.
      let overlayEnabled = true;
      toolbar.find('[data-toggle="overlay"]').on('click', function () {
        overlayEnabled = !overlayEnabled;
        $('body').toggleClass('cpa-overlay-disabled', !overlayEnabled);
        $(this).text(overlayEnabled ? 'Disable Overlay' : 'Enable Overlay');
      });
    },

    wrapComponents: function (components) {
      // For demo purposes, we'll attach to common Drupal regions and blocks.
      // In production, this would hook into the actual component rendering.

      const $body = $('body');

      // Find blocks and regions.
      // Arguments are: 1. Arbitrary key, 2. CSS selector, 3. context.
      const elements = once('cpa-component', '[id^="block-"], .block, [data-drupal-selector]', document);
      elements.forEach(function (element, index) {
        // Note that element is an HTML element, not a jQuery object, so
        // it's wrapped in a jQuery wrapper.
        const $element = $(element);
        const elementId = $element.attr('id') || 'component-' + index;

        // Try to find matching component data.
        let componentData = components[elementId];

        // If no exact match, use first component for demo.
        if (!componentData && Object.keys(components).length > 0) {
          const keys = Object.keys(components);
          componentData = components[keys[index % keys.length]];
        }

        if (!componentData) {
          return;
        }

        // Wrap element.
        $element.wrap('<div class="cpa-component-wrapper" data-cpa-id="' + elementId + '"></div>');
        const $wrapper = $element.parent('.cpa-component-wrapper');

        // Add performance class based on duration.
        const duration = componentData.duration || 0;
        if (duration < 10) {
          $wrapper.addClass('cpa-component-excellent');
        } else if (duration < 50) {
          $wrapper.addClass('cpa-component-good');
        } else if (duration < 100) {
          $wrapper.addClass('cpa-component-warning');
        } else {
          $wrapper.addClass('cpa-component-slow');
        }

        // Create tooltip.
        const tooltip = this.createTooltip(componentData);

        // Add hover effect.
        $wrapper.on('mouseenter', function (e) {
          const $tooltip = $(tooltip);
          $body.append($tooltip);

          // Position tooltip.
          const offset = $wrapper.offset();
          $tooltip.css({
            top: offset.top - $tooltip.outerHeight() - 10,
            left: offset.left
          });
        });

        $wrapper.on('mouseleave', function () {
          $('.cpa-tooltip').remove();
        });
      }.bind(this));
    },

    createTooltip: function (data) {
      const cacheStatus = data.cache_status || 'unknown';
      const cacheClass = 'cpa-cache-' + cacheStatus;

      const queryCount = data.query_count || 0;
      const duration = data.duration || 0;

      let durationClass = 'cpa-metric-good';
      if (duration > 100) {
        durationClass = 'cpa-metric-slow';
      } else if (duration > 50) {
        durationClass = 'cpa-metric-warning';
      }

      let queryClass = 'cpa-metric-good';
      if (queryCount > 10) {
        queryClass = 'cpa-metric-slow';
      } else if (queryCount > 5) {
        queryClass = 'cpa-metric-warning';
      }

      let queriesList = '';
      if (data.queries && data.queries.length > 0) {
        queriesList = '<div class="cpa-queries-list">';
        queriesList += '<div style="font-size: 11px; color: #b4b9be; margin-bottom: 4px;">Top Queries:</div>';

        // Show top 3 slowest queries.
        const topQueries = data.queries
          .sort((a, b) => b.time - a.time)
          .slice(0, 3);

        topQueries.forEach(q => {
          const queryText = q.query.length > 80
            ? q.query.substring(0, 80) + '...'
            : q.query;
          queriesList += `<div class="cpa-query-item">${queryText} (${q.time.toFixed(2)}ms)</div>`;
        });

        queriesList += '</div>';
      }

      return `
        <div class="cpa-tooltip">
          <div class="cpa-tooltip-header">
            <span class="cpa-tooltip-title">${data.label || data.id}</span>
            <span class="cpa-cache-status ${cacheClass}">${cacheStatus}</span>
          </div>
          <div class="cpa-metric">
            <span class="cpa-metric-label">Type:</span>
            <span class="cpa-metric-value">${data.type || 'unknown'}</span>
          </div>
          <div class="cpa-metric">
            <span class="cpa-metric-label">Duration:</span>
            <span class="cpa-metric-value ${durationClass}">${duration.toFixed(2)} ms</span>
          </div>
          <div class="cpa-metric">
            <span class="cpa-metric-label">Queries:</span>
            <span class="cpa-metric-value ${queryClass}">${queryCount}</span>
          </div>
          ${data.cache_tags && data.cache_tags.length > 0 ? `
          <div class="cpa-metric">
            <span class="cpa-metric-label">Cache Tags:</span>
            <span class="cpa-metric-value">${data.cache_tags.length}</span>
          </div>
          ` : ''}
          ${queriesList}
        </div>
      `;
    }
  };

})(jQuery, Drupal, drupalSettings, once);
