/**
 * @file
 * Behaviors for the athora theme.
 */

(function ($, _, Drupal, drupalSettings) {
    'use strict';

    Drupal.behaviors.athora = {
        attach: function (context) {
            // Move panels ipe tray padding buttom by it's outer height.
            $('#panels-ipe-tray').parent('body').css('padding-bottom', $('#panels-ipe-tray').outerHeight());

            // Fix toolbar top space load of the page.
            var toolbarTabOuterHeight = $('#toolbar-bar').find('.toolbar-tab').outerHeight() || 0;
            var toolbarTrayHorizontalOuterHeight = $('.is-active.toolbar-tray-horizontal').outerHeight() || 0;
            var topSpace = toolbarTabOuterHeight + toolbarTrayHorizontalOuterHeight;
            $('body').css({'padding-top': topSpace});
            $('.widget-icon', context).click(function () {
                if ($(this).closest('.sticky-widget-wrapper').hasClass('collapsed')) {
                    $(this).closest('.sticky-widget-wrapper').removeClass('collapsed')
                } else {
                    $(this).closest('.sticky-widget-wrapper').addClass('collapsed')
                }
            });

            //TODO: Merge 2 methods below.
            $('.broker-search-form').find('.form-autocomplete').on('autocompleteclose', function (event, node) {
                var val = $(this).val();
                var match = val.match(/\((.*?)\)$/);
                if (match) {
                    $(this).data('real-value', val);
                    $(this).val(val.replace(' ' + match[0], ''));
                }
            });

            $('.broker-search-form').find('.form-autocomplete').each(function (event, node) {
                var val = $(this).val();
                var match = val.match(/\((.*?)\)$/);
                if (match) {
                    $(this).data('real-value', val);
                    $(this).val(val.replace(' ' + match[0], ''));
                }
            });
        }
    };

})(window.jQuery, window._, window.Drupal, window.drupalSettings);
