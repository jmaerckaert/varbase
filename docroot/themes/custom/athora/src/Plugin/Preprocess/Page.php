<?php

namespace Drupal\athora\Plugin\Preprocess;

use Drupal\bootstrap\Utility\Variables;
use Drupal\bootstrap\Plugin\Preprocess\Page as BootstrapPage;

/**
 * Pre-processes variables for the "page" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("page")
 */
class Page extends BootstrapPage {

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables) {
    parent::preprocessVariables($variables);
    $variables['logo_print_athora'] = \Drupal::request()->getBaseUrl() . '/' . \Drupal::service('extension.list.theme')->getPath('athora') . '/logo-print.svg';
    // Check if page has background image.
    $variables['page']['has_background_image'] = FALSE;
    try {
      $background_image_manager = \Drupal::service('background_image.manager');
      if (($background_image = $background_image_manager->getBackgroundImage()) && $background_image->getImageFile()) {
        $variables['page']['has_background_image'] = TRUE;
      }
    }
    catch (\Exception $ex) {
      \Drupal::logger('athora')->error($ex->getMessage());
    }
  }

}
