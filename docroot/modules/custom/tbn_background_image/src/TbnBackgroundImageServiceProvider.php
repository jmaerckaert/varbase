<?php

namespace Drupal\tbn_background_image;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

class TbnBackgroundImageServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->hasDefinition('background_image.manager')) {
      $definition = $container->getDefinition('background_image.manager');
      $definition->setClass('Drupal\tbn_background_image\BackgroundImageManager');
    }
  }

}
