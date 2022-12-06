"use strict";

(function ($, Drupal) {
  Drupal.behaviors.tbnSearchBehavior = {
    attach: function (context, settings) {
      $('.search-use-ajax', context).once('ajax').each(function(i, ajaxLink) {
        var $linkElement = $(ajaxLink);

        var elementSettings = {
          progress: { type: 'none' },
          base: $linkElement.attr('id'),
          element: ajaxLink,
        };
        var href = $linkElement.attr('href');

        /**
         * For anchor tags, these will go to the target of the anchor rather
         * than the usual location.
         */
        if (href) {
          elementSettings.url = href;
          elementSettings.event = 'click';
        }
        var ajax = Drupal.ajax(elementSettings);
        ajax.options.beforeSend = function (xmlhttprequest, options) {
          var self = this;
          options.data = options.data + '&keys=' + $(ajaxLink).closest('.search-modal-block-form').find('input').val();
          Drupal.Ajax.prototype.beforeSend.call(self, xmlhttprequest, options);
        };

      });

      $('.search-modal-input', context).once('ajax').each(function(i, ajaxInput) {
        $(ajaxInput).keydown(function (e) {
          if (e.which === 13) {
            $(this).closest('.search-modal-block-form').children('.search-use-ajax').click();
          }
        });
      });

      // Prevent form submit.
      $('form.search-form').on('submit', function(e) {
        e.preventDefault();
      });

      var $input = $('input.delayed-input-submit', context);
      if ($($input).length) {
        var ajaxDelayed = findAjaxInstance($input);

        if (ajaxDelayed) {
          if (ajaxDelayed.options.tbnSearch === undefined) {
            ajaxDelayed.options.tbnSearch = true;
          }
          ajaxDelayed.options.success = function(response, status) {
            Drupal.Ajax.prototype.success.call(ajaxDelayed, response, status);
            $input.focus();
          };
        }

        if($input.val()) {
          $input.trigger("delayed_input");
        }
        var timeout = null;
        var delay = $input.data('delay') || 300;
        var triggerEvent = $input.data('event') || "delayed_input";
        $input.unbind('keyup').keyup(function () {
          clearTimeout(timeout);
          timeout = setTimeout(function () {
            if($input.val()) {
              $input.trigger(triggerEvent);
            }
          }, delay);
        });
      }
      function findAjaxInstance(element) {
        var ajax = null;
        var selector = '#' + element.attr('id');
        for (var index in Drupal.ajax.instances) {
          var ajaxInstance = Drupal.ajax.instances[index];
          if (ajaxInstance && (ajaxInstance.selector === selector)) {
            ajax = ajaxInstance;
            break;
          }
        }
        return ajax;
      }

    }
  };
})(jQuery, Drupal);
