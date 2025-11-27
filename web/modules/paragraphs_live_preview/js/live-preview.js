/**
 * @file
 * Paragraphs Live Preview - Main JavaScript.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.paragraphsLivePreview = {

    settings: null,
    $previewPane: null,
    $previewIframe: null,
    $form: null,
    debounceTimer: null,
    isInitialized: false,

    init: function() {
      if (this.isInitialized) {
        return;
      }

      this.settings = drupalSettings.paragraphsLivePreview || {};

      if (!this.settings.enabled) {
        return;
      }

      this.$form = $('form.node-form');

      if (this.$form.length === 0) {
        return;
      }

      this.createPreviewPane();
      this.attachEventListeners();
      this.isInitialized = true;

      console.log('Paragraphs Live Preview initialized');
    },

    createPreviewPane: function() {
      const self = this;

      const $toggleBtn = $('<button>')
        .attr({
          'type': 'button',
          'class': 'preview-toggle-btn'
        })
        .text('Toggle Preview')
        .on('click', function() {
          self.togglePreview();
        });

      this.$previewPane = $('<div>')
        .attr({
          'id': 'live-preview-pane',
          'class': 'live-preview-pane hidden'
        });

      const $toolbar = $('<div>')
        .attr('class', 'preview-toolbar')
        .append($toggleBtn)
        .append(this.createBreakpointButtons());

      this.$previewIframe = $('<iframe>')
        .attr({
          'class': 'preview-iframe',
          'title': 'Live Preview'
        });

      const $loading = $('<div>')
        .attr('class', 'preview-loading')
        .text('Loading preview...')
        .hide();

      this.$previewPane
        .append($toolbar)
        .append($loading)
        .append(this.$previewIframe);

      this.$form.parent().append(this.$previewPane);
    },

    createBreakpointButtons: function() {
      const self = this;
      const breakpoints = {
        mobile: 375,
        tablet: 768,
        desktop: 1200
      };

      const $container = $('<div>').attr('class', 'preview-breakpoints');

      $.each(breakpoints, function(name, width) {
        const $btn = $('<button>')
          .attr({
            'type': 'button',
            'class': 'breakpoint-btn',
            'data-width': width
          })
          .text(name.charAt(0).toUpperCase() + name.slice(1))
          .on('click', function() {
            self.setBreakpoint(width);
            $container.find('.breakpoint-btn').removeClass('active');
            $(this).addClass('active');
          });

        $container.append($btn);
      });

      $container.find('.breakpoint-btn').last().addClass('active');

      return $container;
    },

    togglePreview: function() {
      this.$previewPane.toggleClass('hidden');

      if (!this.$previewPane.hasClass('hidden')) {
        this.updatePreview();
      }
    },

    setBreakpoint: function(width) {
      this.$previewIframe.css('max-width', width + 'px');
    },

    attachEventListeners: function() {
      const self = this;

      this.$form.on('input change', 'input, textarea, select', function() {
        self.scheduleUpdate();
      });
    },

    scheduleUpdate: function() {
      const self = this;

      clearTimeout(this.debounceTimer);

      this.debounceTimer = setTimeout(function() {
        self.updatePreview();
      }, this.settings.debounceDelay || 500);
    },

    updatePreview: function() {
      const self = this;

      this.$previewPane.find('.preview-loading').show();

      const formData = this.collectFormData();

      $.ajax({
        url: this.settings.previewUrl,
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
          if (response.success && response.html) {
            self.renderPreview(response.html);
          } else {
            console.error('Preview failed:', response.error);
          }
        },
        error: function(xhr, status, error) {
          console.error('Preview AJAX error:', error);
        },
        complete: function() {
          self.$previewPane.find('.preview-loading').hide();
        }
      });
    },

    collectFormData: function() {
      const formData = {};

      const nodeId = this.$form.find('input[name="nid"]').val();
      if (nodeId) {
        formData.node_id = nodeId;
      }

      const serialized = this.$form.serializeArray();

      formData.form_data = {};
      $.each(serialized, function(index, field) {
        formData.form_data[field.name] = field.value;
      });

      const nodeType = this.$form.attr('class').match(/node-([a-z-]+)-form/);
      if (nodeType && nodeType[1]) {
        formData.form_data.type = nodeType[1];
      }

      return formData;
    },

    renderPreview: function(html) {
      const iframeDoc = this.$previewIframe[0].contentDocument ||
        this.$previewIframe[0].contentWindow.document;

      iframeDoc.open();
      iframeDoc.write(html);
      iframeDoc.close();
    }

  };

  Drupal.behaviors.paragraphsLivePreview = {
    attach: function(context, settings) {
      $('form.node-form', context).once('paragraphs-live-preview').each(function() {
        Drupal.paragraphsLivePreview.init();
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
