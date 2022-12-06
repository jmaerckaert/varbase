(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.tbnLanguageCookieJsCookieRedirectBehavior = {
    attach: function (context, settings) {
      $('.language-link').each(function(){
        $(this).on("click", function(e) {
          e.preventDefault();
          var language = $(this).data('language');
          if (!language) {
            language = drupalSettings.tbn_language_cookie.default_language;
          }
          Drupal.tbn_language_cookie.setStatus(language);
          var path = $(this).attr('href');
          Drupal.tbn_language_cookie.redirect(path);
        });
      });


      if (Drupal.tbn_language_cookie.showLanguageSelector()) {
        $('.block-language-selection-page').removeClass('hidden');
      }
    }
  };

  Drupal.tbn_language_cookie = {};

  Drupal.tbn_language_cookie.showLanguageSelector = function () {
    var showLanguageSelector = false;
    var status = Drupal.tbn_language_cookie.getCurrentStatus();
    if (status === null || status === undefined) {
      showLanguageSelector = true;
    }

    return showLanguageSelector;
  };

  Drupal.tbn_language_cookie.getCurrentStatus = function () {
    var cookieName = (drupalSettings.tbn_language_cookie.cookie.name === '') ? 'language' : drupalSettings.tbn_language_cookie.cookie.name;
    return $.cookie(cookieName);
  };

  Drupal.tbn_language_cookie.setStatus = function (status) {
    var date = new Date();
    var domain = drupalSettings.tbn_language_cookie.cookie.domain ? drupalSettings.tbn_language_cookie.cookie.domain : '';
    var path = drupalSettings.tbn_language_cookie.cookie.path ? drupalSettings.tbn_language_cookie.cookie.path : '';
    var cookieName = (drupalSettings.tbn_language_cookie.cookie.name === '') ? 'language' : drupalSettings.tbn_language_cookie.cookie.name;
    if (path.length > 1) {
      var pathEnd = path.length - 1;
      if (path.lastIndexOf('/') === pathEnd) {
        path = path.substring(0, pathEnd);
      }
    }

    var lifetime = parseInt(drupalSettings.tbn_language_cookie.cookie.expire);
    date.setTime(date.getTime() + lifetime * 1000); // * 1000 for milliseconds
    $.cookie(cookieName, status, { expires: date, path: path, domain: domain, secure: true});
  };


  Drupal.tbn_language_cookie.redirect = function(path) {
    if (path.substr(0, 4) !== 'http') {
      path = location.protocol + "//" + location.host + path;
    }
    if (navigator.appName === "Microsoft Internet Explorer" && (parseFloat(navigator.appVersion.split("MSIE")[1])) < 7) {
      window.location.href = path;
    }
    else {
      window.location = path;
    }
  };

})(jQuery, Drupal, drupalSettings);
