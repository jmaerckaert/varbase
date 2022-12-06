/**
 * @file
 * Behaviors for the athora theme.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.athora = {
    attach: function (context) {
      $(window).once().bind("load", function () {
        var hash = window.location.hash;
        if ($(hash).closest('.ui-accordion-content').siblings('h3').attr('aria-expanded') === 'false') {
          $(hash).closest('.ui-accordion-content').siblings('h3').trigger('click');
        }
        if ($(hash).hasClass('ui-accordion-header') && $(hash).attr('aria-expanded') === 'false') {
          $(hash).trigger('click');
        }
        $(hash).collapse({
          toggle: true
        });
      });
    }
  };

}(jQuery, Drupal));
