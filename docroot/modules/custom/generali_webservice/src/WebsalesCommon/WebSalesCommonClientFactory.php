<?php

namespace Drupal\generali_webservice\WebsalesCommon;

use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderHandler;
use GoetasWebservices\SoapServices\SoapClient\Metadata\Loader\MetadataLoaderInterface;
use GoetasWebservices\XML\WSDLReader\Exception\PortNotFoundException;
use GoetasWebservices\XML\WSDLReader\Exception\ServiceNotFoundException;
use GuzzleHttp\HandlerStack;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
use JMS\Serializer\SerializerInterface;
use webignition\Guzzle\Middleware\HttpAuthentication\AuthorizationType;
use webignition\Guzzle\Middleware\HttpAuthentication\CredentialsFactory;
use webignition\Guzzle\Middleware\HttpAuthentication\HostComparer;
use webignition\Guzzle\Middleware\HttpAuthentication\HttpAuthenticationMiddleware;
use GoetasWebservices\SoapServices\SoapClient\Client;

class WebSalesCommonClientFactory
{
  /**
   * @var SerializerInterface
   */
  protected $serializer;
  /**
   * @var MessageFactory
   */
  protected $messageFactory;

  /**
   * @var HttpClient
   */
  protected $httpClient;

  /**
   * @var MetadataLoaderInterface
   */
  private $reader;

  /**
   * @var HeaderHandler
   */
  private $headerHandler;

  public function __construct(MetadataLoaderInterface $reader, SerializerInterface $serializer)
  {
    $this->setMetadataReader($reader);
    $this->setSerializer($serializer);
  }

  public function setHeaderHandler(HeaderHandler $headerHandler)
  {
    $this->headerHandler = $headerHandler;
  }

  public function setHttpClient(HttpClient $client)
  {
    $this->httpClient = $client;
  }

  /**
   * @param MessageFactory $messageFactory
   */
  public function setMessageFactory(MessageFactory $messageFactory)
  {
    $this->messageFactory = $messageFactory;
  }

  public function setSerializer(SerializerInterface $serializer)
  {
    $this->serializer = $serializer;
  }

  public function setMetadataReader(MetadataLoaderInterface $reader)
  {
    $this->reader = $reader;
  }

  private function getSoapService($wsdl, $portName = null, $serviceName = null)
  {
    $servicesMetadata = $this->reader->load($wsdl);
    $service = $this->getService($serviceName, $servicesMetadata);
    return $this->getPort($portName, $service);
  }

  /**
   * @param string $wsdl
   * @param null|string $portName
   * @param null|string $serviceName
   * @return Client
   */
  public function getClient($wsdl, $portName = null, $serviceName = null)
  {
    $service = $this->getSoapService($wsdl, $portName, $serviceName);

    $this->httpClient = $this->httpClient ?: HttpClientDiscovery::find();
    $this->messageFactory = $this->messageFactory ?: MessageFactoryDiscovery::find();
    $headerHandler = $this->headerHandler ?: new HeaderHandler();

    // Creating a client that uses the middleware
    $httpAuthenticationMiddleware = new HttpAuthenticationMiddleware(new HostComparer());
    $handlerStack = HandlerStack::create();
    $handlerStack->push($httpAuthenticationMiddleware, 'http-auth');

    $this->httpClient = $this->httpClient::createWithConfig([
      'handler' => $handlerStack,
    ]);

    // Setting credentials on the middleware
    $username = \Drupal::config('generali_webservice.settings')->get('websalescommon_user');
    $password = \Drupal::config('generali_webservice.settings')->get('websalescommon_pass');
    $credentials = CredentialsFactory::createBasicCredentials($username, $password);

    $httpAuthenticationMiddleware->setType(AuthorizationType::BASIC);
    $httpAuthenticationMiddleware->setCredentials($credentials);
    $httpAuthenticationMiddleware->setHost(\Drupal::config('generali_webservice.settings')->get('websalescommon_host'));

    return new Client($service, $this->serializer, $this->messageFactory, $this->httpClient, $headerHandler);
  }

  /**
   * @param $serviceName
   * @param array $services
   * @return array
   * @throws ServiceNotFoundException
   */
  private function getService($serviceName, array $services)
  {
    if ($serviceName && isset($services[$serviceName])) {
      return $services[$serviceName];
    } elseif ($serviceName) {
      throw new ServiceNotFoundException("The service named $serviceName can not be found");
    } else {
      return reset($services);
    }
  }

  /**
   * @param string $portName
   * @param array $service
   * @return array
   * @throws PortNotFoundException
   */
  private function getPort($portName, array $service)
  {
    if ($portName && isset($service[$portName])) {
      return $service[$portName];
    } elseif ($portName) {
      throw new PortNotFoundException("The port named $portName can not be found");
    } else {
      return reset($service);
    }
  }
}
