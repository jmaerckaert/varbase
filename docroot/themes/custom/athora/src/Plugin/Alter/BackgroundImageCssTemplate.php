<?php

namespace Drupal\athora\Plugin\Alter;

use Drupal\bootstrap\Plugin\Alter\AlterInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Implements hook_theme_suggestions_alter().
 *
 * @ingroup plugins_alter
 *
 * @BootstrapAlter("background_image_css_template")
 */
class BackgroundImageCssTemplate extends PluginBase implements AlterInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(&$variables, &$template_filename = NULL, &$background_image = NULL) {
    /** @var \Drupal\Core\Entity\EntityInterface $target */
    $target = $background_image->getTargetEntity();
    if ($target && $target->bundle() === 'epartner') {
      $fmdm = \Drupal::service('file_metadata_manager');
      /** @var \Drupal\file\FileInterface $image */
      $image = $background_image->getImageFile();
      /** @var \Drupal\file_mdm\FileMetadataInterface $file_metadata */
      $file_metadata = $fmdm->uri($image->getFileUri());
      $metadata = $file_metadata->getMetadata('getimagesize');
      // Index 0 and 1 contains respectively the width and the height of the image.
      $variables['ratio'] = $metadata[1]/$metadata[0]*100;
      $template_filename = \Drupal::service('extension.list.theme')->getPath('athora') . '/templates/background_image/background_image_epartner.css.twig';
    }
  }
}
