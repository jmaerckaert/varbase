<?php

namespace Drupal\athora_broker\EventSubscriber;

use Drupal\generali_webservice\WebsalesCommon\Php\FrontNameTypes\UserKeyType;
use Drupal\generali_webservice\WebsalesCommon\WebSalesCommonSoapClientFactory;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate_plus\Event\MigrateEvents;
use Drupal\migrate_plus\Event\MigratePrepareRowEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Defines excel migration pre import event subscriber class.
 */
class EPartnerExcelMigrationPrePareRowEvent implements EventSubscriberInterface {

  /**
   * Soap client factory.
   *
   * @var \Drupal\generali_webservice\WebsalesCommon\WebSalesCommonSoapClientFactory
   */
  protected $webSalesCommonSoapClientFactory;

  /**
   * EPartnerExcelMigrationPrePareRowEvent constructor.
   *
   * @param \Drupal\generali_webservice\WebsalesCommon\WebSalesCommonSoapClientFactory $web_sales_common_soap_client_factory
   *   A web sales common soap client factory
   */
  public function __construct(WebSalesCommonSoapClientFactory $web_sales_common_soap_client_factory) {
    $this->webSalesCommonSoapClientFactory = $web_sales_common_soap_client_factory;
  }

  /**
   * Set the event Broker parameter.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event.
   */
  public function onMigrationPrePareRowImport(MigratePrepareRowEvent $event): void {
    /** @var \Drupal\migrate\Plugin\MigrationInterface $migration */
    $migration = $event->getMigration();

    if ($migration->id() === 'epartner_excel') {
      $row = $event->getRow();
      try {
        /** @var GoetasWebservices\SoapServices\SoapClient\Client $client */
        $client = $this->webSalesCommonSoapClientFactory->getClient();

        $broker = new UserKeyType();
        $broker->setUserType($row->getSourceProperty('constants/broker_user_type'));
        $source_ids = $row->getSourceIdValues();
        $broker_id_colom = $row->getSourceProperty('constants/broker_user_name');
        if (isset($source_ids[$broker_id_colom])) {
          $broker->setUserNumber((int) $source_ids[$broker_id_colom]);
        }

        $fields = $row->getSourceProperty('broker_detail_fields');
        if ($fields) {
          $broker_detail = $client->retrieveBrokerDetails($broker);
          /** @var \Drupal\generali_webservice\WebsalesCommon\Php\FrontNameTypes\ResponseResultType $response */
          $response = $broker_detail->getResponseResult();
          if ($response->getResult() === 'OK') {
            $broker_data = $broker_detail->getBrokerData();
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            foreach ($fields as $element_name) {
              $value = $propertyAccessor->getValue($broker_data, $element_name);
              if ($value) {
                $row->setSourceProperty($element_name, $value);
              }
            }
          }
          else {
            $error = $response->getError();
            throw new \Exception(t('Could not retrieve broker [@broker] detail: @reason', ['@broker' => $broker->getUserNumber() ,'@reason' => $error->getErrorReason()]));
          }
        }
      }
      catch (\Exception $e) {
        throw new MigrateSkipRowException($e->getMessage(), FALSE);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::PREPARE_ROW] = 'onMigrationPrePareRowImport';
    return $events;
  }

}