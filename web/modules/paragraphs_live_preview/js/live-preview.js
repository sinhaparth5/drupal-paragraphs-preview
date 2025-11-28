/**
 * @file
 * Paragraphs Live Preview - Main JavaScript.
 */

(function ($, Drupal, drupalSettings, once) {
  'use strict';

  /**
   * Paragraphs Live Preview Manager (Singleton).
   */
  Drupal.paragraphsLivePreview = Drupal.paragraphsLivePreview || {

    settings: null,
    $previewPane: null,
    $previewIframe: null,
    $form: null,
    debounceTimer: null,
    isInitialized: false,

    /**
     * Initialize the live preview system.
     */
    init: function(formElement) {
      // Prevent multiple initializations.
      if (this.isInitialized) {
        console.log('Paragraphs Live Preview: Already initialized, skipping');
        return;
      }

      // Get settings from drupalSettings.
      this.settings = drupalSettings.paragraphsLivePreview || {};

      if (!this.settings.enabled) {
        console.log('Paragraphs Live Preview: Disabled in settings');
        return;
      }

      // Use the passed form element or find it.
      this.$form = formElement ? $(formElement) : $('form.node-form').first();

      if (this.$form.length === 0) {
        console.log('Paragraphs Live Preview: No node form found');
        return;
      }

      console.log('Paragraphs Live Preview: Initializing...', {
        form: this.$form[0],
        settings: this.settings
      });

      try {
        this.createPreviewPane();
        this.attachEventListeners();
        this.isInitialized = true;
        console.log('Paragraphs Live Preview: Successfully initialized');
      }
      catch (error) {
        console.error('Paragraphs Live Preview: Initialization failed', error);
      }
    },

    /**
     * Create the preview pane UI.
     */
    createPreviewPane: function() {
      const self = this;

      // Check if pane already exists.
      if (document.getElementById('live-preview-pane')) {
        console.log('Paragraphs Live Preview: Pane already exists, reusing');
        this.$previewPane = $('#live-preview-pane');
        this.$previewIframe = this.$previewPane.find('.preview-iframe');
        return;
      }

      // Create FLOATING toggle button (always visible on right edge)
      const $floatingToggle = $('<button>')
        .attr({
          'type': 'button',
          'class': 'preview-floating-toggle',
          'aria-label': 'Toggle live preview pane'
        })
        .text('Live Preview')
        .on('click', function(e) {
          e.preventDefault();
          self.togglePreview();
        });

      // Create toggle button in toolbar
      const $toggleBtn = $('<button>')
        .attr({
          'type': 'button',
          'class': 'preview-toggle-btn',
          'aria-label': 'Close live preview pane'
        })
        .text('Close Preview')
        .on('click', function(e) {
          e.preventDefault();
          self.togglePreview();
        });

      // Create preview pane container.
      this.$previewPane = $('<div>')
        .attr({
          'id': 'live-preview-pane',
          'class': 'live-preview-pane hidden',
          'role': 'complementary',
          'aria-label': 'Live preview panel'
        });

      // Create toolbar.
      const $toolbar = $('<div>')
        .attr('class', 'preview-toolbar')
        .append($toggleBtn)
        .append(this.createBreakpointButtons());

      // Create loading indicator.
      const $loading = $('<div>')
        .attr({
          'class': 'preview-loading',
          'role': 'status',
          'aria-live': 'polite'
        })
        .text('Loading preview...')
        .hide();

      // Create iframe for preview.
      this.$previewIframe = $('<iframe>')
        .attr({
          'class': 'preview-iframe',
          'title': 'Live Preview',
          'aria-label': 'Content preview'
        });

      // Assemble preview pane.
      this.$previewPane
        .append($toolbar)
        .append($loading)
        .append(this.$previewIframe);

      // Add both to body.
      $('body').append(this.$previewPane).append($floatingToggle);

      console.log('Paragraphs Live Preview: Preview pane created');
    },

    /**
     * Create breakpoint toggle buttons.
     */
    createBreakpointButtons: function() {
      const self = this;
      const breakpoints = {
        mobile: 375,
        tablet: 768,
        desktop: 1200
      };

      const $container = $('<div>')
        .attr({
          'class': 'preview-breakpoints',
          'role': 'group',
          'aria-label': 'Preview viewport sizes'
        });

      $.each(breakpoints, function(name, width) {
        const $btn = $('<button>')
          .attr({
            'type': 'button',
            'class': 'breakpoint-btn',
            'data-width': width,
            'aria-label': 'Preview in ' + name + ' size (' + width + 'px)'
          })
          .text(name.charAt(0).toUpperCase() + name.slice(1))
          .on('click', function(e) {
            e.preventDefault();
            self.setBreakpoint(width);
            $container.find('.breakpoint-btn').removeClass('active').attr('aria-pressed', 'false');
            $(this).addClass('active').attr('aria-pressed', 'true');
          });

        $container.append($btn);
      });

      // Activate desktop by default.
      $container.find('.breakpoint-btn').last().addClass('active').attr('aria-pressed', 'true');

      return $container;
    },

    /**
     * Toggle preview pane visibility.
     */
    togglePreview: function() {
      this.$previewPane.toggleClass('hidden');

      const isVisible = !this.$previewPane.hasClass('hidden');

      if (isVisible) {
        console.log('Paragraphs Live Preview: Opening preview pane');
        this.updatePreview();
      }
      else {
        console.log('Paragraphs Live Preview: Closing preview pane');
      }
    },

    /**
     * Set preview breakpoint width.
     */
    setBreakpoint: function(width) {
      console.log('Paragraphs Live Preview: Setting breakpoint to ' + width + 'px');
      this.$previewIframe.css('max-width', width + 'px');
    },

    /**
     * Attach event listeners to form fields.
     */
    attachEventListeners: function() {
      const self = this;

      // Remove old listeners first.
      this.$form.off('.paragraphsPreview');

      // Listen to all input changes in the form.
      this.$form.on('input.paragraphsPreview change.paragraphsPreview', 'input, textarea, select', function() {
        console.log('Paragraphs Live Preview: Form field changed', this.name);
        self.scheduleUpdate();
      });

      // Listen to paragraph operations.
      this.$form.on('click.paragraphsPreview', '.paragraphs-add-wrapper button, .paragraphs-dropdown-actions button', function() {
        console.log('Paragraphs Live Preview: Paragraph operation detected');
        setTimeout(function() {
          self.scheduleUpdate();
        }, 500);
      });

      console.log('Paragraphs Live Preview: Event listeners attached');
    },

    /**
     * Schedule preview update with debouncing.
     */
    scheduleUpdate: function() {
      const self = this;

      clearTimeout(this.debounceTimer);

      this.debounceTimer = setTimeout(function() {
        console.log('Paragraphs Live Preview: Debounce complete, updating preview');
        self.updatePreview();
      }, this.settings.debounceDelay || 500);
    },

    /**
     * Update the preview iframe.
     */
    updatePreview: function() {
      const self = this;

      // Don't update if preview pane is hidden.
      if (this.$previewPane.hasClass('hidden')) {
        console.log('Paragraphs Live Preview: Skipping update (pane is hidden)');
        return;
      }

      console.log('Paragraphs Live Preview: Starting preview update');

      // Show loading indicator.
      this.$previewPane.find('.preview-loading').show();

      // Collect form data.
      const formData = this.collectFormData();

      console.log('Paragraphs Live Preview: Form data collected', formData);

      // Send AJAX request.
      $.ajax({
        url: this.settings.previewUrl,
        method: 'POST',
        data: formData,
        dataType: 'json',
        timeout: 10000,
        success: function(response) {
          console.log('Paragraphs Live Preview: AJAX success', response);
          if (response.success && response.html) {
            self.renderPreview(response.html);
          }
          else {
            console.error('Paragraphs Live Preview: Preview generation failed', response.error);
            self.showError('Preview generation failed: ' + (response.error || 'Unknown error'));
          }
        },
        error: function(xhr, status, error) {
          console.error('Paragraphs Live Preview: AJAX error', {
            status: status,
            error: error,
            responseText: xhr.responseText
          });
          self.showError('Preview update failed. Check console for details.');
        },
        complete: function() {
          self.$previewPane.find('.preview-loading').hide();
        }
      });
    },

    /**
     * Collect form data for preview.
     */
    collectFormData: function() {
      // Get node ID if editing existing node.
      const nodeId = this.$form.find('input[name="nid"]').val();

      // Get form token for CSRF protection
      const formToken = this.$form.find('input[name="form_token"]').val();

      // Serialize form - this creates a proper format for Drupal
      const serialized = this.$form.serialize();

      // Build proper data object
      const data = {
        serialized_form: serialized
      };

      if (nodeId) {
        data.node_id = nodeId;
      }

      if (formToken) {
        data.form_token = formToken;
      }

      // Get node type
      const nodeType = this.$form.attr('class').match(/node-([a-z0-9_-]+)-form/);
      if (nodeType && nodeType[1]) {
        data.node_type = nodeType[1];
      }

      console.log('Collected form data:', data);

      return data;
    },

    /**
     * Render HTML in preview iframe.
     */
    renderPreview: function(html) {
      try {
        const iframeDoc = this.$previewIframe[0].contentDocument ||
          this.$previewIframe[0].contentWindow.document;

        iframeDoc.open();
        iframeDoc.write(html);
        iframeDoc.close();

        console.log('Paragraphs Live Preview: Preview rendered successfully');
      }
      catch (error) {
        console.error('Paragraphs Live Preview: Failed to render preview', error);
        this.showError('Failed to render preview');
      }
    },

    /**
     * Show error message in preview pane.
     */
    showError: function(message) {
      const $error = $('<div>')
        .attr('class', 'preview-error')
        .text(message);

      this.$previewIframe.hide();
      this.$previewPane.find('.preview-error').remove();
      this.$previewPane.append($error);

      const self = this;
      setTimeout(function() {
        $error.fadeOut(500, function() {
          $(this).remove();
        });
        self.$previewIframe.show();
      }, 3000);
    }

  };

  /**
   * Drupal behavior for Paragraphs Live Preview.
   */
  Drupal.behaviors.paragraphsLivePreview = {
    attach: function(context, settings) {
      // Use modern once API - arguments: key, selector, context.
      const elements = once('paragraphs-live-preview', 'form.node-form', context);

      if (elements.length === 0) {
        return;
      }

      console.log('Paragraphs Live Preview: Found ' + elements.length + ' form(s) to process');

      // Only initialize once with the first form found.
      if (elements.length > 0 && !Drupal.paragraphsLivePreview.isInitialized) {
        Drupal.paragraphsLivePreview.init(elements[0]);
      }
    }
  };

})(jQuery, Drupal, drupalSettings, once);
