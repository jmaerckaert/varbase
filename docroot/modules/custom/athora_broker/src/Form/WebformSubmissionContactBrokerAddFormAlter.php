<?php

namespace Drupal\athora_broker\Form;

/**
 * Defines alterations to the contact-broker webform.
 */
class WebformSubmissionContactBrokerAddFormAlter {

  /**
   * Implements hook_form_alter().
   */
  public function alterForm(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id) {
    $form['actions']['back'] = array (
      '#type' => 'button',
      '#value' => t('Go Back'),
      '#attributes' => array(
        'class' => [
          'button-back'
        ],
        'onclick' => 'window.history.go(-1)',
      )
    );
  }
}
