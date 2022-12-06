<?php

namespace Drupal\athora\Plugin\Preprocess;

use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\bootstrap\Utility\Variables;
use Drupal\language_cookie\Plugin\LanguageNegotiation\LanguageNegotiationCookie;

/**
 * Pre-processes variables for the "html" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("html")
 */
class Html extends PreprocessBase {

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables) {
    $request = \Drupal::request();
    $variables['base_path'] = $request->getSchemeAndHttpHost();
    if (!\Drupal::service('language_negotiator')->getNegotiationMethodInstance(LanguageNegotiationCookie::METHOD_ID)->getLangcode($request)) {
      $variables->addClass('no-scroll');
    }
  }

}
