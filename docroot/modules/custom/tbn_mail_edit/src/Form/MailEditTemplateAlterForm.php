<?php

namespace Drupal\tbn_mail_edit\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Define MailEditTemplateAlterForm class.
 */
class MailEditTemplateAlterForm {

  /**
   * Contains the configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The configuration factory object.
   */
  public function __construct(ConfigFactoryInterface $config) {
    $this->configFactory = $config;
  }

  /**
   * Add the 'to' setting.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param string $form_id
   */
  public function alterForm(&$form, FormStateInterface $form_state, $form_id): void {
    $id = $form_state->getBuildInfo()['args'][0] ?? NULL;
    [$config_name, $name] = $this->parseId($id);
    $config = $this->configFactory->get($config_name);

    // Add field to configure the mail to param.
    $form['message']['to'] = [
      '#type' => 'email',
      '#title' => t('To'),
      '#weight' => -2,
      '#default_value' => $config ? $config->get($name . '.to') : '',
    ];

    $form['#submit'][] = [$this, 'submitForm'];
  }

  /**
   * Save the 'to' setting.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array $form, FormStateInterface $form_state): void {
    // Work out the template ID.
    $id = $form_state->getValue('id');
    [$config_name, $name] = $this->parseId($id);

    // Get the config object for this template.
    $configFactory = $this->configFactory->getEditable($config_name);

    // Update the config object.
    $configFactory->set($name . '.to', $form_state->getValue('to'));
    $configFactory->save();
  }

  /**
   * Parse the given id.
   *
   * @param string $id
   *
   * @return array
   */
  private function parseId($id): array {
    $parts = explode('.', $id);
    $config_name = $parts[0] . '.' . $parts[1];
    unset($parts[0], $parts[1]);
    $name = implode('.', $parts);

    return [$config_name, $name];
  }

}
