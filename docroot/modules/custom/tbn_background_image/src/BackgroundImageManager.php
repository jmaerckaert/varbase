<?php

namespace Drupal\tbn_background_image;

use Drupal\background_image\BackgroundImageManager as BackgroundImageManagerBase;
use Drupal\views\ViewEntityInterface;

/**
 * Define BackgroundImageManager class.
 */
class BackgroundImageManager extends BackgroundImageManagerBase {

  /**
   * {@inheritdoc}
   */
  public function getViewBackgroundImage(ViewEntityInterface $view, $langcode = NULL, array $context = []) {
    $entity = parent::getViewBackgroundImage($view, $langcode, $context);


    $context['langcode'] = $langcode;
    $this->moduleHandler->alter('tbn_background_image_view_background_image', $entity, $view, $context);

    return $entity;
  }

}
