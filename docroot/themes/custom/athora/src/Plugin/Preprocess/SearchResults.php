<?php

namespace Drupal\athora\Plugin\Preprocess;

use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\bootstrap\Utility\Variables;
use Drupal\language_cookie\Plugin\LanguageNegotiation\LanguageNegotiationCookie;

/**
 * Pre-processes variables for the "item_list__search_results" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("item_list__search_results")
 */
class SearchResults extends PreprocessBase {

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables) {
    // The $pager_total_items variable is a global
    // array keyed by the pager element increments.
    global $pager_total_items;

    // Assuming you have one paged list on your page only,
    // the element's key should be 0.
    $variables['total_items_count'] = $pager_total_items[0] ?? 0;
    $variables['keys'] = 'test'; // $plugin->getKeywords();
  }

}
