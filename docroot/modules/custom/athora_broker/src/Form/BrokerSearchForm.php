<?php

namespace Drupal\athora_broker\Form;

use Drupal\athora_broker\Helper\BrokerWebsiteUrlParser;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\generali_webservice\WebsalesCommon\WebSalesCommonSoapClientFactory;
use Drupal\leaflet\LeafletService;
use Drupal\taxonomy\Entity\Term;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\generali_webservice\WebsalesCommon\Php\ExternalWebservice01\FindBroker2Request\SearchAroundLocalityAType;
use Drupal\generali_webservice\WebsalesCommon\Php\FrontNameTypes\GeographicalCoordinatesType;
use Drupal\generali_webservice\WebsalesCommon\Php\FrontNameTypes\AddressType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the class that is responsible for the Broker search form.
 */
class BrokerSearchForm extends FormBase {

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
   * @var BrokerWebsiteUrlParser
   */
  protected $websiteUrlParser;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   *   The HTTP request object.
   */
  protected $request;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * BrokerSearchForm constructor.
   *
   * @param WebSalesCommonSoapClientFactory $web_sales_common_soap_client_factory
   * @param LeafletService $leaflet_service
   * @param Renderer $renderer
   * @param BrokerWebsiteUrlParser $website_url_parser
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(WebSalesCommonSoapClientFactory $web_sales_common_soap_client_factory, LeafletService $leaflet_service, Renderer $renderer, BrokerWebsiteUrlParser $website_url_parser, Request $request, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, LoggerInterface $logger) {
    $this->clientFactory = $web_sales_common_soap_client_factory;
    $this->leafletService = $leaflet_service;
    $this->renderer = $renderer;
    $this->websiteUrlParser = $website_url_parser;
    $this->request = $request;
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('generali_webservice.web_sales_common_soap_client_factory'),
      $container->get('leaflet.service'),
      $container->get('renderer'),
      $container->get('athora_broker.broker_website_url_parser'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('logger.factory')->get('generali_webservice'),
    );
  }

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'broker_search_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['accommodation'] = [
      '#type' => 'container',
      '#attributes' => array(
        'class' => ['container'],
      ),
    ];

    $form['accommodation']['description'] = [
      '#type' => 'item',
      '#markup' => $this->t(
        'Select you nearest broker'
      ),
      '#prefix' => '<h2>',
      '#suffix' => '</h2>',
    ];

    $location_entity = NULL;
    if ($location_id = $this->request->query->get('location')) {
      $location_entity = Term::load($location_id);
    }
    $form['accommodation']['location'] = [
      '#title' => $this->t('Location'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'taxonomy_term',
      '#selection_settings' => array(
        'target_bundles' => array('localities'),
      ),
      '#default_value' => $location_entity ?? NULL,
      '#attributes' => [
        'class' => [
          'input-location'
        ]
      ],
    ];

    $form['accommodation']['actions'] = [
      '#type' => 'actions',
    ];

    $form['accommodation']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Find broker'),
    ];

    $type = $this->request->query->get('type', 'save');
    if ($location_id && $type) {
      $map = leaflet_map_get_info('OSM Mapnik');
      $lat = $location_entity->hasField('field_latitude') ? $location_entity->get('field_latitude')->value : NULL;
      $lon = $location_entity->hasField('field_longitude') ? $location_entity->get('field_longitude')->value : NULL;
      if (!empty($lat) && !empty($lon)) {
        $map['settings']['center']['lat'] = $lat;
        $map['settings']['center']['lon'] = $lon;
        $map['settings']['map_position_force'] = TRUE;
        $map['settings']['zoom'] = '12';
      }
      $form['map'] = $this->leafletService->leafletRenderMap($map, $this->buildFeatures($type, $location_id), '600px');
      $form['#attached']['library'][] = 'athora_broker/athora_broker.leaflet';
      $form['#attached']['drupalSettings']['athora_broker']['broker'] = $this->request->query->get('broker');
    }

    return $form;
  }

  /**
   * Validate the title and the checkbox of the form
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if (empty($form_state->getValue('location'))) {
      $form_state->setErrorByName('location', t('Please enter a valid location.'));
    }
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $url = Url::fromRoute('athora_broker.broker_search_form')->setOption(
      'query', [
        'type' => $this->request->query->get('type', 'save'),
        'location' => $form_state->getValue('location'),
        ]
    );
    $form_state->setRedirectUrl($url);
  }

  /**
   * Build the features array needed to render the markers on the map.
   *
   * @param string $type
   * @param int $location_tid
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function buildFeatures(string $type, int $location_tid): array {
    $search_around_locality = new SearchAroundLocalityAType();
    $search_around_x_y = new GeographicalCoordinatesType();
    $search_around_address = new AddressType();

    $location_term = Term::load($location_tid);
    if (!$location_term) {
      return [];
    }

    $postal = trim(substr($location_term->getName(), 0, 5));
    $municipality = trim(substr($location_term->getName(), 5, strlen($location_term->getName())));
    $search_around_locality->setSpotZipCode($postal);
    $search_around_locality->setSpotLocality($municipality);

    $features = [];
    try {
      /** @var \Drupal\generali_webservice\WebsalesCommon\Php\ExternalWebservice01\SoapStubs\ICommonService $client */
      $client = $this->clientFactory->getClient();
      $language_code = $this->languageManager->getCurrentLanguage()->getId();
      $response = $client->findBroker2($type, FALSE, 50, 10, $search_around_locality, $search_around_x_y, $search_around_address, 10, 'DistanceFromSpot', $language_code,TRUE);

      // Collect e-partner pages for brokers without a website URL.
      $epartner_pages = $this->websiteUrlParser->retrievePartnerPages($response->getBrokerData());

      foreach ($response->getBrokerData() as $broker) {
        $website = $broker->getWebsiteURL() === NULL ? $epartner_pages[$broker->getBroker()->getUserNumber()] : $this->websiteUrlParser->parseWebsiteUrl($broker->getWebsiteURL());
        $popup = [
          '#theme' => 'athora-broker-info-summary',
          '#broker_name' => $broker->getBrokerName(),
          '#broker_address' => $broker->getBrokeraddress()->getAddress(),
          '#broker_street_number' => $broker->getBrokeraddress()->getStreetNumber(),
          '#broker_locality_zip' => $broker->getBrokeraddress()->getLocality()->getZipCode(),
          '#broker_locality_name' => $broker->getBrokeraddress()->getLocality()->getName(),
          '#broker_phone' => $broker->getPhoneNumber1(),
          '#broker_fax' => $broker->getFax(),
          '#broker_website' => $website,
          '#broker_contact' => Url::fromUri('internal:/webform/contact_broker', [
            'query' => [
              'broker' => $broker->getBroker()->getUserNumber(),
              'type' => $type,
              'location' => $location_tid,
            ]
          ]),
        ];
        $features[] = [
          'type' => 'point',
          'icon' => TRUE,
          'lat' => $broker->getBrokerAddressXY()->getY(),
          'lon' => $broker->getBrokerAddressXY()->getX(),
          'label' => $broker->getBrokerName(),
          'popup' => $this->renderer->render($popup),
          'popup_options' => [
            'minWidth' => '100%',
          ],
          'broker' => $broker->getBroker()->getUserNumber(),
        ];
      }
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }

    return $features;
  }

}
