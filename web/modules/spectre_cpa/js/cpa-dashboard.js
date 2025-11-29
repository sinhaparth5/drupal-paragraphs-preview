/**
 * @file
 * JavaScript for CPA dashboard.
 */

(function ($, Drupal, once) {
  'use strict';

  Drupal.behaviors.cpaDashboard = {
    attach: function (context, settings) {
      // Dashboard interactions can be added here.
      // For example: sorting tables, filtering components, etc.

      // Add click handlers for table rows.
      // Arguments are: 1. Arbitrary key, 2. CSS selector, 3. context.
      const elements = once('cpa-dashboard', '.cpa-slowest table tr', context);
      elements.forEach(function (element) {
        // Note that element is an HTML element, not a jQuery object, so
        // it's wrapped in a jQuery wrapper.
        const $row = $(element);
        $row.on('click', function () {
          $(this).toggleClass('highlighted');
        });
      });
    }
  };

})(jQuery, Drupal, once);
