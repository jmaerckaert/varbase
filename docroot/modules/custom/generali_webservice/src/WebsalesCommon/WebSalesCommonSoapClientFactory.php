<?php

namespace Drupal\generali_webservice\WebsalesCommon;

use GoetasWebservices\SoapServices\SoapClient\Builder\SoapContainerBuilder;
use GoetasWebservices\SoapServices\SoapClient\Client;
use GoetasWebservices\SoapServices\SoapClient\ClientFactory;
use Drupal\Core\Extension\ModuleExtensionList;

class WebSalesCommonSoapClientFactory {

  /**
   * @var ClientFactory
   */
  private $factory;

  /**
   * The Module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList;
   */
  protected $moduleExtensionList;

  /**
   * GeneraliSoapClientFactory constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(ModuleExtensionList $moduleExtensionList) {
    $container = new SoapClientContainer();
    $serializer = SoapContainerBuilder::createSerializerBuilderFromContainer($container)->build();
    $metadata = $container->get('goetas_webservices.soap_client.metadata_reader');
    $this->factory = new WebSalesCommonClientFactory($metadata, $serializer);
    $this->moduleExtensionList = $moduleExtensionList;
  }

  /**
   * @return \GoetasWebservices\SoapServices\SoapClient\Client
   */
  public function getClient(): Client {
    return $this->factory->getClient($this->moduleExtensionList->getPath('generali_webservice') . '/config/WebsalesCommon/CommonExternalWS01.wsdl');
  }

}
