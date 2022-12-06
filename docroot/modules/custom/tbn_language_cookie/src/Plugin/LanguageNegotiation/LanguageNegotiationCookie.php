<?php

namespace Drupal\tbn_language_cookie\Plugin\LanguageNegotiation;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Drupal\language\LanguageNegotiatorInterface;
use Drupal\language\LanguageSwitcherInterface;
use Drupal\language\Plugin\LanguageNegotiation\LanguageNegotiationUrl;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\language_cookie\Plugin\LanguageNegotiation\LanguageNegotiationCookie as LanguageNegotiationCookieBase;

/**
 * Class override for identifying language from a language cookie.
 */
class LanguageNegotiationCookie extends LanguageNegotiationCookieBase implements LanguageSwitcherInterface {

  /**
   * The language negotiator.
   *
   * @var \Drupal\language\LanguageNegotiatorInterface
   */
  protected $negotiator;

  /**
   * {@inheritdoc}
   *
   * @param LanguageNegotiatorInterface $language_negotiator
   *   The language negotiator.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageNegotiatorInterface $negotiator) {
    parent::__construct($config_factory);
    $this->negotiator = $negotiator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_negotiator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguageSwitchLinks(Request $request,$type,Url $url){
    $links = $this->negotiator->getNegotiationMethodInstance(LanguageNegotiationUrl::METHOD_ID)->getLanguageSwitchLinks($request, $type, $url);

    $config = $this->configFactory->getEditable('language_cookie.negotiation');
    if ($config->get('js_cookie_redirect')) {
      foreach ($links as $langcode => &$link) {
        $link['attributes']['data-language'] = $langcode;
      }
    }

    return $links;
  }

}
