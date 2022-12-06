<?php

namespace Drupal\tbn_language_cookie\Plugin\LanguageCookieCondition;

use Drupal\Core\Form\FormStateInterface;
use Drupal\language_cookie\LanguageCookieConditionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class for the JS cookie redirect condition plugin.
 *
 * @LanguageCookieCondition(
 *   id = "js_cookie_redirect",
 *   weight = -200,
 *   name = @Translation("JS Cookie Redirect"),
 *   description = @Translation("Enable Javascript-based language cookie redirect.")
 * )
 */
class LanguageCookieConditionJsCookieRedirect extends LanguageCookieConditionBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (isset($this->configuration[$this->getPluginId()])) {
      return !$this->configuration[$this->getPluginId()] ? $this->pass() : $this->block();
    }

    return $this->pass();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form[$this->getPluginId()] = [
      '#title' => t('Enable Javascript-based language cookie redirect.'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration[$this->getPluginId()] ?? NULL,
      '#description' => t('Redirect requests to the language selection page using the language saved in the language cookie of the visitor. This may be useful if you use page caching systems such as Boost or Varnish.'),
    ];

    return $form;
  }

}
