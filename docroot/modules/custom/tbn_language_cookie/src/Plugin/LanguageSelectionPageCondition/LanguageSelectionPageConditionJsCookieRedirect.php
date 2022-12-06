<?php

namespace Drupal\tbn_language_cookie\Plugin\LanguageSelectionPageCondition;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\language_selection_page\LanguageSelectionPageConditionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class for the Language Cookie condition plugin.
 *
 * @LanguageSelectionPageCondition(
 *   id = "js_cookie_redirect",
 *   weight = -109,
 *   name = @Translation("JS Cookie Redirect"),
 *   runInBlock = TRUE,
 * )
 */
class LanguageSelectionPageConditionJsCookieRedirect extends LanguageSelectionPageConditionBase {

  /**
   * Contains the configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * LanguageSelectionPageConditionLanguageCookie constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The configuration factory object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ConfigFactoryInterface $config, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function alterPageContent(array &$content = [], $destination = '<front>') {
    parent::alterPageContent($content, $destination);

    $config = $this->configFactory->get('language_cookie.negotiation');
    if (isset($content[0]['#language_links']) && $config->get($this->getPluginId())) {

      foreach ($content[0]['#language_links']['#items'] as $langcode => &$item) {
        $item['#attributes']['class'][] = 'language-link';
        $item['#attributes']['data-language'] = $langcode;
      }
      unset($item);
      $content[0]['#attached']['drupalSettings']['tbn_language_cookie'] = [
        'cookie' => [
          'name' => $config->get('param'),
          'expire' => $config->get('time'),
          'path' => $config->get('path'),
          'domain' => $config->get('domain'),
        ],
        'set_on_every_pageload' => $config->get('set_on_every_pageload'),
        'default_language' => $this->languageManager->getDefaultLanguage()->getId(),
      ];
      $content[0]['#attached']['library'][] = 'tbn_language_cookie/cookie-redirect';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    return $this->pass();
  }

}
