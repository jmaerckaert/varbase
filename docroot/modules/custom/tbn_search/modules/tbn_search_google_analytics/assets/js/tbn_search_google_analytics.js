"use strict";

(function ($, Drupal, drupalSettings) {
  $(document).ajaxComplete(function(event,xhr,options){
    if (typeof options.tbnSearch === 'undefined' || !options.tbnSearch || drupalSettings.tbnSearchGoogleAnalytics === undefined) {
      return;
    }
    gtag('config', drupalSettings.google_analytics.account, {
      page_path: drupalSettings.tbnSearchGoogleAnalytics.pagePath + '?search=' + options.extraData.keys,
      page_title: drupalSettings.tbnSearchGoogleAnalytics.pageTitle
    });

    // Fetch the autocomplete items and register an onclick event to send the data to GA.
    $('#search-results-wrapper .item-list a').each(function() {
      $( this ).on('click', function() {
        gtag('event', 'view_item', {
          'event_category': 'engagement',
          'event_label': $(this).text() + ' | ' + $(this).attr('href')
        });
      });
    });
  });
})(jQuery, Drupal, drupalSettings);
