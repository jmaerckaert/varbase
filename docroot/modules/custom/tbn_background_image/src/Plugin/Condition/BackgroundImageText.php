<?php

namespace Drupal\tbn_background_image\Plugin\Condition;

use Drupal\background_image\BackgroundImageManagerInterface;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Background image text' condition.
 *
 * @Condition(
 *   id = "background_image_text",
 *   label = @Translation("Background image text"),
 * )
 */
class BackgroundImageText extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\background_image\BackgroundImageManagerInterface
   */
  protected $backgroundImageManager;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\background_image\BackgroundImageManagerInterface $background_image_manager
   *   The Background Image Manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BackgroundImageManagerInterface $background_image_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->backgroundImageManager = $background_image_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('background_image.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (!empty($this->configuration['negate'])) {
      return $this->t("If the background image text isn't provided.");
    }

    return $this->t('If the background image text is provided.');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if ($background_image = $this->backgroundImageManager->getBackgroundImage()) {
      return (bool) $background_image->getText();
    }

    return TRUE;
  }

}
