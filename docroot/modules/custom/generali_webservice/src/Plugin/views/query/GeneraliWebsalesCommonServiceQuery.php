<?php

namespace Drupal\generali_webservice\Plugin\views\query;

use Drupal\generali_webservice\WebsalesCommon\WebSalesCommonSoapClientFactory;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\generali_webservice\WebsalesCommon\Php\ExternalWebservice01\SoapStubs\ICommonService;

/**
 *  Defines a Views query class for the Generali Websales Common Service.
 *
 *  @ViewsQuery(
 *   id = "generali_websales_common_service_query",
 *   title = @Translation("Generali Websales Common Service"),
 *   help = @Translation("Defines a Views query class for the Generali Websales Common Service.")
 *  )
 */
class GeneraliWebsalesCommonServiceQuery extends QueryPluginBase {

  /**
   * A list of tables in the order they should be added, keyed by alias.
   */
  protected $tableQueue = [];

  /**
   * An array of fields.
   */
  protected $fields = [];

  /**
   * An array mapping table aliases and field names to field aliases.
   */
  protected $fieldAliases = [];

  /**
   * An array of sections of the WHERE query.
   *
   * Each section is in itself an array of pieces and a flag as to whether
   * or not it should be AND or OR.
   */
  protected $where = [];

  /**
   * A simple array of order by clauses.
   */
  protected $orderby = [];

  /**
   * The default operator to use when connecting the WHERE groups.
   */
  protected $groupOperator = 'AND';

  /**
   * @var ICommonService $client
   */
  private $client;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, WebSalesCommonSoapClientFactory $web_sales_common_soap_client_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->client = $web_sales_common_soap_client_factory->getClient();
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
    );
  }

  /**
   * Builds the necessary info to execute the query.
   */
  public function build(ViewExecutable $view) {
    // Store the view in the object to be able to use it later.
    $this->view = $view;

    $view->initPager();

    // Let the pager modify the query to add limits.
    $view->pager->query();

    $view->build_info['query'] = $this->query();
    $view->build_info['count_query'] = $this->query(TRUE);
  }

  /**
   * Make sure table exists.
   *
   * @param string $table
   *   Table name.
   * @param string $relationship
   *   Relationship.
   * @param string $join
   *   Join.
   */
  public function ensureTable($table, $relationship = NULL, $join = NULL) {}

  /**
   * Add a metric or dimension to the query.
   *
   * @param string $table
   *   NULL in most cases, we could probably remove this altogether.
   * @param string $field
   *   The name of the metric/dimension/field to add.
   * @param string $alias
   *   Probably could get rid of this too.
   * @param array $params
   *   Probably could get rid of this too.
   *
   * @return string
   *   The name that this field can be referred to as.
   */
  public function addField($table, $field, $alias = '', $params = []) {
    // We check for this specifically because it gets a special alias.
    if ($table == $this->view->storage->get('base_table') && $field == $this->view->storage->get('base_field') && empty($alias)) {
      $alias = $this->view->storage->get('base_field');
    }

    if ($table && empty($this->tableQueue[$table])) {
      $this->ensureTable($table);
    }

    if (!$alias && $table) {
      $alias = $table . '_' . $field;
    }

    // Make sure an alias is assigned.
    $alias = $alias ? $alias : $field;

    // We limit the length of the original alias up to 60 characters
    // to get a unique alias later if its have duplicates.
    $alias = substr($alias, 0, 60);

    // Create a field info array.
    $field_info = [
        'field' => $field,
        'table' => $table,
        'alias' => $alias,
      ] + $params;

    // Test to see if the field is actually the same or not. Due to
    // differing parameters changing the aggregation function, we need
    // to do some automatic alias collision detection:
    $base = $alias;
    $counter = 0;
    while (!empty($this->fields[$alias]) && $this->fields[$alias] != $field_info) {
      $field_info['alias'] = $alias = $base . '_' . ++$counter;
    }

    if (empty($this->fields[$alias])) {
      $this->fields[$alias] = $field_info;
    }

    // Keep track of all aliases used.
    $this->fieldAliases[$table][$field] = $alias;

    return $alias;
  }

  /**
   * Add a filter string to the query.
   *
   * @param string $group
   *   The filter group to add these to; Groups cannot be nested.
   *   Use 0 as the default group.  If the group does not yet exist it will
   *   be created as an AND group.
   * @param string $field
   *   The name of the metric/dimension/field to check.
   * @param mixed $value
   *   The value to test the field against.
   * @param string $operator
   *   The comparison operator, such as =, <, or >=.
   */
  public function addWhere($group, $field, $value = NULL, $operator = NULL) {
    // Ensure all variants of 0 are actually 0. Thus '', 0 and NULL are all
    // the default group.
    if (empty($group)) {
      $group = 0;
    }

    // Check for a group.
    if (!isset($this->where[$group])) {
      $this->setWhereGroup('AND', $group);
    }

    $this->where[$group]['conditions'][] = [
      'field' => $field,
      'value' => $value,
      'operator' => $operator,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query($get_count = FALSE) {
    $query = [];

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function execute(ViewExecutable $view) {
    $start = microtime(TRUE);

    $search_around_locality = new \Drupal\generali_webservice\WebsalesCommon\Php\ExternalWebservice01\FindBroker2Request\SearchAroundLocalityAType();
    $search_around_x_y = new \Drupal\generali_webservice\WebsalesCommon\Php\FrontNameTypes\GeographicalCoordinatesType();
    $search_around_address = new \Drupal\generali_webservice\WebsalesCommon\Php\FrontNameTypes\AddressType();

    foreach ($view->filter as $name => $filter) {
      switch ($name) {
        case 'broker_locality_zip_code':
//          if (!empty($filter->value)) {
            $search_around_locality->setSpotZipCode(3500);
            $search_around_locality->setSpotLocality('Hasselt');
//          }
          break;
      }
    }
    $search_around_locality->setSpotZipCode(3500);
    $search_around_locality->setSpotLocality('Hasselt');
    $response = $this->client->findBroker2('HOME', FALSE, 10, 1, $search_around_locality, $search_around_x_y, $search_around_address, 10, 'DistanceFromSpot', 'nl',TRUE);
//    $response_localities = $this->client->getListLocalities('CPOS');
//    $localities_options = [];
//    foreach ($response_localities->getLocality() as $locality) {
//
//    }

    $views_result = [];
    $count = 0;
    foreach ($response->getBrokerData() as $broker) {
      $row['index'] = $count;
      $row['broker_broker_user_number'] = $broker->getBroker()->getUserNumber();
      $row['broker_broker_user_type'] = $broker->getBroker()->getUserType();
      $row['broker_broker_name'] = $broker->getBrokerName();
      $row['broker_address_address'] = $broker->getBrokeraddress()->getAddress();
      $row['broker_address_street_number'] = $broker->getBrokeraddress()->getStreetNumber();
      $row['broker_address_post_box'] = $broker->getBrokeraddress()->getPostBox();
      $row['broker_locality_zip_code'] = $broker->getBrokeraddress()->getLocality()->getZipCode();
      $row['broker_locality_name'] = $broker->getBrokeraddress()->getLocality()->getName();
      $row['broker_address_country'] = $broker->getBrokeraddress()->getCountry();
      $row['broker_coordinates_x'] = $broker->getBrokerAddressXY()->getX();
      $row['broker_coordinates_y'] = $broker->getBrokerAddressXY()->getY();
      $row['broker_cbfa'] = $broker->getCBFA();
      $row['broker_fsma'] = $broker->getFSMA();
      $row['broker_phone_number_1'] = $broker->getPhoneNumber1();
      $row['broker_phone_number_2'] = $broker->getPhoneNumber2();
      $row['broker_gsm'] = $broker->getGsm();
      $row['broker_fax'] = $broker->getFax();
      $row['broker_email'] = $broker->getEmail();
      $row['broker_email_branch_lead'] = $broker->getEmailBranchLead();
      $row['broker_website_url'] = $broker->getWebsiteURL();
      $row['broker_language'] = $broker->getLanguage();
      $row['broker_language_code'] = $broker->getLanguageCode();
      $row['broker_brocom'] = $broker->getBrocomYN() ? 'Yes' : 'No';
      $row['broker_gforce'] = $broker->getGForceYN() ? 'Yes' : 'No';
      $row['broker_logo_url'] = $broker->getLogoURL();
      $row['broker_broker_stars'] = $broker->getBrokerStars();
      $row['broker_enterprise_number'] = $broker->getEntrepriseNumber();
      $views_result[] = new ResultRow($row);
      $count++;
    }
    $view->result = $views_result;
    $view->execute_time = microtime(TRUE) - $start;
  }
}