<?php

namespace Drupal\athora\Plugin\Alter;

use Drupal\bootstrap\Plugin\Alter\AlterInterface;
use Drupal\bootstrap\Plugin\PluginBase;

/**
 * Implements hook_bootstrap_layouts_class_options_alter().
 *
 * @ingroup plugins_alter
 *
 * @BootstrapAlter("bootstrap_layouts_class_options")
 */
class BootstrapLayoutsClassOptions extends PluginBase implements AlterInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(&$data, &$context1 = NULL, &$context2 = NULL): void {
    if (!isset($data['utility']['container'])) {
      $data['utility']['container'] = $this->t('Container');
      $data['utility']['container-wrapper'] = $this->t('Container wrapper');
      $data['utility']['bg-white'] = $this->t('Background white');
      $data['utility']['flex-container-faq'] = $this->t('FAQ container');
    }

    // unset unused classes for usability
    foreach (['columns', 'hidden', 'visible', 'background', 'text_alignment', 'text_color', 'text_transformation'] as $key) {
      if (isset($data[$key])) {
        unset($data[$key]);
      }
    }
    unset($data['utility']['clearfix']);
  }

}