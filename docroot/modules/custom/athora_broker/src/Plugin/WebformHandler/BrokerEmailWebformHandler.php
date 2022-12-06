<?php

namespace Drupal\athora_broker\Plugin\WebformHandler;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\generali_webservice\WebsalesCommon\Php\FrontNameTypes\UserKeyType;
use Drupal\generali_webservice\WebsalesCommon\WebSalesCommonSoapClientFactory;
use Drupal\webform\Plugin\WebformElementManagerInterface;
use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;
use Drupal\webform\WebformSubmissionConditionsValidatorInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformThemeManagerInterface;
use Drupal\webform\WebformTokenManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Emails a webform submission.
 *
 * @WebformHandler(
 *   id = "broker_email",
 *   label = @Translation("Broker email"),
 *   category = @Translation("Notification"),
 *   description = @Translation("Sends a webform submission to the broker email address."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class BrokerEmailWebformHandler extends EmailWebformHandler {

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * @var \Drupal\generali_webservice\WebsalesCommon\WebSalesCommonSoapClientFactory
   */
  protected $webSalesCommonSoapClientFactory;

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, WebformSubmissionConditionsValidatorInterface $conditions_validator, AccountInterface $current_user, ModuleHandlerInterface $module_handler, LanguageManagerInterface $language_manager, MailManagerInterface $mail_manager, WebformThemeManagerInterface $theme_manager, WebformTokenManagerInterface $token_manager, WebformElementManagerInterface $element_manager, Request $request, WebSalesCommonSoapClientFactory $web_sales_common_soap_client_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger_factory, $config_factory, $entity_type_manager, $conditions_validator, $current_user, $module_handler, $language_manager, $mail_manager, $theme_manager, $token_manager, $element_manager);
    $this->request = $request;
    $this->webSalesCommonSoapClientFactory = $web_sales_common_soap_client_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('webform_submission.conditions_validator'),
      $container->get('current_user'),
      $container->get('module_handler'),
      $container->get('language_manager'),
      $container->get('plugin.manager.mail'),
      $container->get('webform.theme_manager'),
      $container->get('webform.token_manager'),
      $container->get('plugin.manager.webform.element'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('generali_webservice.web_sales_common_soap_client_factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['to']['to_mail'] = [
      '#type' => 'item',
      '#title' => $this->t('To email'),
      '#markup' => $this->t('Broker email address')
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function sendMessage(WebformSubmissionInterface $webform_submission, array $message) {
    $message['to_mail'] = NULL;
    $broker_id = $this->request->query->get('broker');
    if ($broker_id) {
      /** @var \Drupal\generali_webservice\WebsalesCommon\Php\FrontNameTypes\BrokerDataType $broker */
      $broker = $this->getBroker($broker_id);
      $message['to_mail'] = $broker ? $broker->getEmail() :  NULL;
    }

    parent::sendMessage($webform_submission, $message);
  }

  /**
   * Get broker by id.webform.webform.contact_broker
   *
   * @param int $broker_id
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\node\Entity\Node|mixed|null
   */
  private function getBroker(int $broker_id) {
    /** @var \Drupal\generali_webservice\WebsalesCommon\Php\ExternalWebservice01\SoapStubs\ICommonService $client */
    $client = $this->webSalesCommonSoapClientFactory->getClient();

    $broker = new UserKeyType();
    $broker->setUserNumber($broker_id);
    $broker->setUserType('BRO');

    $response = $client->retrieveBrokerDetails($broker);
    $broker_details = $response->getBrokerData();
    return $broker_details ?? NULL;
  }

}