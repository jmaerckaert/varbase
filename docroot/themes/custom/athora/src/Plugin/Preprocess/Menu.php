<?php

namespace Drupal\athora\Plugin\Preprocess;

use Drupal\bootstrap\Plugin\Preprocess\Menu as BootstrapMenu;
use Drupal\bootstrap\Utility\Variables;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\menu_item_extras\Utility\Utility;
use Drupal\social_media_links\Plugin\SocialMediaLinks\Platform\Drupal;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy_menu\Plugin\Menu\TaxonomyMenuMenuLink;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Pre-processes variables for the "menu" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("menu")
 */
class Menu extends BootstrapMenu implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The core language manager.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, LanguageManager $language_manager, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function preprocessVariables(Variables $variables) {
    parent::preprocessVariables($variables);
    $this->addMenuIcon($variables->items);
    if (!empty($variables['menu_name']) && Utility::checkBundleHasExtraFieldsThanEntity('menu_link_content', $variables['menu_name'])) {
      foreach ($variables->items as &$item) {
        $entity = $item['entity'];
        if ($entity->hasField('field_icon') && !$entity->get('field_icon')->isEmpty()) {
          $item['icon'] = $entity->get('field_icon')->view('icon');
        }
      }
    }
  }

  /**
   * Add menu icon when configured on taxonomy term.
   *
   * @param array $items
   */
  private function addMenuIcon(array &$items) {
    foreach ($items as &$item) {
      $original_link = $item['original_link'];
      if (!$original_link instanceof TaxonomyMenuMenuLink) {
        continue;
      }

      try {
        $entity = $this->entityTypeManager->getStorage('taxonomy_term')->load($original_link->pluginDefinition['metadata']['taxonomy_term_id']);
        if (!$entity instanceof Term) {
          continue;
        }

        $entity = $entity->getTranslation($this->languageManager->getCurrentLanguage()->getId());
        if ($entity->hasField('field_icon') && !$entity->get('field_icon')->isEmpty()) {
          $media = $entity->get('field_icon')->view('icon');
          if ($media) {
            $item['icon'] = $media;
          }
        }
        if (!empty($entity->getDescription())) {
          $item['description'] = ['#markup' => $entity->getDescription()];
        }
      }
      catch (\Exception $e) {
        $this->loggerFactory->get('athora')->error($e->getMessage());
      }
    }
  }

}
