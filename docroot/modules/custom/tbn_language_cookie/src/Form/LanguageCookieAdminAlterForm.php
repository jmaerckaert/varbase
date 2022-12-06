<?php

namespace Drupal\tbn_language_cookie\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;

class LanguageCookieAdminAlterForm {

  /**
   * The language cookie negotiation config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * LanguageCookieAdminAlterForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->getEditable('language_cookie.negotiation');
  }

  /**
   * Add submit handler to save the language cookie condition configuration.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param string $form_id
   */
  public function alterForm(&$form, FormStateInterface $form_state, $form_id): void {
    $form['actions']['submit']['#submit'][] = [$this, 'submitForm'];
  }

  /**
   * Submit handler to save the language cookie condition configuration.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(&$form, FormStateInterface $form_state): void {
    /** @var \Drupal\language_cookie\LanguageCookieConditionInterface $condition */
    foreach ($form_state->get(['conditions']) as $condition) {
      $condition->submitConfigurationForm($form, $form_state);
      if (isset($condition->getConfiguration()[$condition->getPluginId()])) {
        $this->config->set($condition->getPluginId(), $condition->getConfiguration()[$condition->getPluginId()]);
      }
    }

    $this->config->save();
  }

}