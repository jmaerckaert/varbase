<?php

namespace Drupal\athora\Plugin\Alter;

use Drupal\bootstrap\Plugin\Alter\ThemeSuggestions as BootstrapThemeSuggestions;
use Drupal\menu_item_extras\Utility\Utility;

/**
 * Implements hook_theme_suggestions_alter().
 *
 * @ingroup plugins_alter
 *
 * @BootstrapAlter("theme_suggestions")
 */
class ThemeSuggestions extends BootstrapThemeSuggestions {

  /**
   * {@inheritdoc}
   */
  public function alter(&$suggestions, &$variables = [], &$hook = NULL):void {
    parent::alter($suggestions, $variables, $hook);
  }

  /**
   * Dynamic alter method for "menu".
   */
  protected function alterMenu(): void {
    // Immediately return if there is a element.
    if ($this->element) {
      return;
    }

    $suggestions = [];
    $variables = $this->variables;
    if (isset($variables['menu_name']) && Utility::checkBundleHasExtraFieldsThanEntity('menu_link_content', $variables['menu_name'])) {
      $menu_name = $variables['menu_name'];
      /** @var \Drupal\menu_item_extras\Utility\Utility $utility */
      $utility = \Drupal::service('menu_item_extras.utility');
      $menu_name = $utility::sanitizeMachineName($menu_name);
      $suggestion_prefix = 'menu__extras';

      // The MenuBlock plugin's support custom plugin name.
      if (!empty($variables['menu_block_configuration'])) {
        $config = $variables['menu_block_configuration'];

        // Add our custom theme suggestion.
        if (!empty($config['suggestion']) && $config['suggestion'] !== $menu_name) {
          $suggestions[] = $utility::suggestion($suggestion_prefix, $menu_name, $config['suggestion']);
        }
      }
    }

    // Add suggestions.
    if ($suggestions) {
      $this->addSuggestion($suggestions);
    }
  }

}
