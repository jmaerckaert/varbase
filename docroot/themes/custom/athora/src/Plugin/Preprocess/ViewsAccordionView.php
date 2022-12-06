<?php

namespace Drupal\athora\Plugin\Preprocess;

use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\bootstrap\Utility\Variables;

/**
 * Pre-processes variables for the "views_accordion_view__management__block_1" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("views_accordion_view__management__block_1")
 */
class ViewsAccordionView extends PreprocessBase {

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables) {
    $view = $variables['view'];
    $rows = $variables['rows'];
    if (empty($rows)) {
      return;
    }

    foreach ($rows as $row_id => $row) {
      /** @var \Drupal\views\ResultRow $result_row */
      $result_row = $row['content']['#row'] ?? [];
      if (!$result_row) {
        continue;
      }

      $fields = [];
      $header_field = FALSE;
      foreach ($view->field as $field_name => $field) {
        $exclude = $field->options['exclude'] ?? 0;
        if ($exclude) {
          continue;
        }
        if (!$header_field && !$exclude) {
          $header_field = TRUE;
          continue;
        }
        // render this even if set to exclude so it can be used elsewhere.
        $field_output = $view->style_plugin->getField($result_row->index, $field_name);
        $fields[$field_name] = $field->isValueEmpty($field_output, $field->options['empty_zero']);
      }

      if (in_array(false, $fields, true) === false) {
        $variables['rows'][$row_id]['attributes']->addClass('empty');
      }
    }
  }

}
