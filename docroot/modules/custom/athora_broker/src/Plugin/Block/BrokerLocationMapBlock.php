<?php

namespace Drupal\athora_broker\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Site\Settings;
use Drupal\generali_webservice\WebsalesCommon\Php\FrontNameTypes\UserKeyType;
use Drupal\generali_webservice\WebsalesCommon\WebSalesCommonSoapClientFactory;
use Drupal\leaflet\LeafletService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Broker Location Map Block.
 *
 * @Block(
 *   id = "athora_broker_broker_location_map_block",
 *   admin_label = @Translation("Broker Location Map Block"),
 *   category = @Translation("Athora"),
 * )
 */
class BrokerLocationMapBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var WebSalesCommonSoapClientFactory
   */
  protected $clientFactory;

  /**
   * @var LeafletService
   */
  protected $leafletService;

  /**
   * @var Renderer
   */
  protected $renderer;

  /**
   * BrokerLocationMapBlock constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param WebSalesCommonSoapClientFactory $web_sales_common_soap_client_factory
   * @param LeafletService $leaflet_service
   * @param Renderer $renderer
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, WebSalesCommonSoapClientFactory $web_sales_common_soap_client_factory, LeafletService $leaflet_service, Renderer $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->clientFactory = $web_sales_common_soap_client_factory;
    $this->leafletService = $leaflet_service;
    $this->renderer = $renderer;
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
      $container->get('leaflet.service'),
      $container->get('renderer')
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

    $map = leaflet_map_get_info('OSM Mapnik');

    $features[] = [
      'type' => 'point',
      'lat' => $broker_details->getBrokerAddressXY()->getY(),
      'lon' => $broker_details->getBrokerAddressXY()->getX(),
      'label' => $broker_details->getBrokerName(),
    ];

    return $this->leafletService->leafletRenderMap($map, $features, '500px');
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
