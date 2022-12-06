<?php

namespace Drupal\generali_webservice\Plugin\migrate_plus\data_parser;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Soap;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Drupal\migrate_plus\DataParserPluginBase;

/**
 * Obtain Websales common SOAP data for migration.
 *
 * @DataParser(
 *   id = "websales_common_soap",
 *   title = @Translation("Websales common SOAP")
 * )
 */
class WebSalesCommonSoap extends Soap {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Webservice default configuration settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Username if http authentication through soap call is required.
   *
   * @var string
   */
  protected $username;

  /**
   * Password if http authentication through soap call is required.
   *
   * @var string
   */
  protected $password;

  /**
   * Response property that contains the data on the SOAP service.
   *
   * @var mixed
   */
  protected $responseProperty;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if (empty($this->urls[$this->activeUrl])) {
      $this->urls = [realpath(\Drupal::service('extension.list.module')->getPath('generali_webservice') . '/config/WebsalesCommon/CommonExternalWS01.wsdl')];
    }
    $this->configFactory = $config_factory;
    $this->config = $this->configFactory->get('generali_webservice.settings');
    $this->username = $configuration['username'] ?? $this->config->get('websalescommon_user');
    $this->password = $configuration['password'] ?? $this->config->get('websalescommon_pass');
    $this->responseProperty = $configuration['response_property'] ?? NULL;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): DataParserPluginBase {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('logger.factory')->get('generali_webservice'),
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \SoapFault
   *   If there's an error in a SOAP call.
   * @throws \Drupal\migrate\MigrateException
   *   If we can't resolve the SOAP function or its response property.
   */
  protected function openSourceUrl($url): bool {
    $options = [
      'login' => $this->username,
      'password' => $this->password,
    ];

    // Will throw SoapFault if there's
    $client = new \SoapClient($url, $options);

    // Determine the response property name.
    $function_found = FALSE;
    foreach ($client->__getFunctions() as $function_signature) {
      // E.g., "GetWeatherResponse GetWeather(GetWeather $parameters)".
      $response_type = strtok($function_signature, ' ');
      $function_name = strtok('(');
      if (strcasecmp($function_name, $this->function) === 0) {
        $function_found = TRUE;
        if ($this->responseProperty) {
          $response_property = $this->responseProperty;
        }
        else {
          foreach ($client->__getTypes() as $type_info) {
            // E.g., "struct GetWeatherResponse {\n string GetWeatherResult;\n}".
            if (preg_match('|struct (.*?) {\s*[a-zA-Z0-9_]+ (.*?);|is', $type_info, $matches)) {
              if ($matches[1] == $response_type) {
                $response_property = $matches[2];
              }
            }
          }
        }
        break;
      }
    }
    if (!$function_found) {
      throw new MigrateException("SOAP function {$this->function} not found.");
    }
    if (!isset($response_property)) {
      throw new MigrateException("Response property not found for SOAP function {$this->function}.");
    }
    $response = $client->{$this->function}($this->parameters);
    $response_value = $response->$response_property;
    $this->iterator = new \ArrayIterator($response_value);
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  protected function fetchNextRow(): void {
    $current = $this->iterator->current();
    if ($current) {
      foreach ($this->fieldSelectors() as $field_name => $selector) {
        try {
          $propertyAccessor = PropertyAccess::createPropertyAccessor();
          $this->currentItem[$field_name] = $propertyAccessor->getValue($current, $selector);
        }
        catch (\Exception $e) {
          $this->logger->error($this->t("Couldn't returns the value at the end of the property path of the object graph: @exception"), ['@exception' => $e->getMessage()]);
        }
      }
      $this->iterator->next();
    }
  }

}
