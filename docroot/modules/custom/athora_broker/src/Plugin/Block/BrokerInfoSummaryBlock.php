<?php

namespace Drupal\athora_broker\Plugin\Block;

use Drupal\athora_broker\Helper\BrokerWebsiteUrlParser;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\generali_webservice\WebsalesCommon\Php\FrontNameTypes\UserKeyType;
use Drupal\generali_webservice\WebsalesCommon\WebSalesCommonSoapClientFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Broker Info Summary Block.
 *
 * @Block(
 *   id = "athora_broker_broker_info_summary_block",
 *   admin_label = @Translation("Broker Info Summary Block"),
 *   category = @Translation("Athora"),
 * )
 */
class BrokerInfoSummaryBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var WebSalesCommonSoapClientFactory
   */
  protected $clientFactory;

  /**
   * @var BrokerWebsiteUrlParser
   */
  protected $websiteUrlParser;

  /**
   * BrokerInfoSummaryBlock constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param WebSalesCommonSoapClientFactory $web_sales_common_soap_client_factory
   * @param BrokerWebsiteUrlParser $website_url_parser
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, WebSalesCommonSoapClientFactory $web_sales_common_soap_client_factory, BrokerWebsiteUrlParser $website_url_parser) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->clientFactory = $web_sales_common_soap_client_factory;
    $this->websiteUrlParser = $website_url_parser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static (
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('generali_webservice.web_sales_common_soap_client_factory'),
      $container->get('athora_broker.broker_website_url_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\generali_webservice\WebsalesCommon\Php\ExternalWebservice01\SoapStubs\ICommonService $client */
    $client = $this->clientFactory->getClient();

    $request = \Drupal::request();
    $broker_id = $request->query->get('broker');
    if (!$broker_id) {
      return [];
    }

    $broker = new UserKeyType();
    $broker->setUserNumber($broker_id);
    $broker->setUserType('BRO');

    $response = $client->retrieveBrokerDetails($broker);
    $broker_details = $response->getBrokerData();

    if (!$broker_details) {
      return [];
    }

    $website = $broker_details->getWebsiteURL() === NULL ? $broker_details->getWebsiteURL() : $this->websiteUrlParser->parseWebsiteUrl($broker_details->getWebsiteURL());
    return [
      '#theme' => 'athora-broker-info-summary',
      '#broker_name' => $broker_details->getBrokerName(),
      '#broker_address' => $broker_details->getBrokeraddress()->getAddress(),
      '#broker_street_number' => $broker_details->getBrokeraddress()->getStreetNumber(),
      '#broker_locality_zip' => $broker_details->getBrokeraddress()->getLocality()->getZipCode(),
      '#broker_locality_name' => $broker_details->getBrokeraddress()->getLocality()->getName(),
      '#broker_phone' => $broker_details->getPhoneNumber1(),
      '#broker_fax' => $broker_details->getFax(),
      '#broker_website' => $website,
      '#broker_contact' => 'mailto:' . $broker_details->getEmail(),
    ];
  }

  /**
   * Do not cache this block.
   *
   * @return int
   */
  public function getCacheMaxAge() {
    return 0;
  }
}
