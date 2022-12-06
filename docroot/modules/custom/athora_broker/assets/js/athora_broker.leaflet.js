(function ($, Drupal, drupalSettings) {
  var broker_feature_mappings = [];

  $(document).on('leaflet.map', function (e, settings, lMap, mapid) {
    var broker = drupalSettings.athora_broker.broker;
    if (broker !== undefined && broker_feature_mappings[broker] !== undefined) {
      $(lMap._layers[broker_feature_mappings[broker]]._icon).once('open-popup').click();
    }
  });

  $(document).on('leaflet.feature', function(e, lFeature, feature) {
    if (feature.broker !== undefined) {
      broker_feature_mappings[feature.broker] = lFeature._leaflet_id;
    }
  });

})(jQuery, Drupal, drupalSettings);