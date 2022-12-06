<?php

namespace Drupal\athora_broker\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\File\Exception\FileWriteException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Define DailyReportMigrateSubscriber class.
 */
class DailyReportEPartnerMigrateSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  private $mailManager;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   Mail manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger channel factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(MailManagerInterface $mail_manager, AccountInterface $current_user, LoggerChannelFactoryInterface $logger, ConfigFactoryInterface $config_factory, DateFormatterInterface $date_formatter, FileSystemInterface $file_system) {
    $this->mailManager = $mail_manager;
    $this->currentUser = $current_user;
    $this->logger = $logger;
    $this->configFactory = $config_factory;
    $this->dateFormatter = $date_formatter;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      MigrateEvents::POST_IMPORT => ['onMigratePostImport', -100],
    ];
  }

  /**
   * Sends an email after ePartner migration is finished.
   *
   * @param MigrateImportEvent $event
   *   Import event.
   */
  public function onMigratePostImport(MigrateImportEvent $event): void {
    $migration = $event->getMigration();
    if ($migration->getBaseId() !== 'epartner_excel') {
      return;
    }
    if ($migration->getStatus() !== MigrationInterface::RESULT_COMPLETED) {
      return;
    }

    if ($migration->allRowsProcessed()) {
      $langcode = $this->currentUser->getPreferredLangcode();
      $config = $this->configFactory->get('athora_broker.emails');
      $to = $config->get('epartner_daily_report.to');

      $params['migration_name'] = $event->getMigration()->label();
      $params['source_count'] = $migration->getSourcePlugin()->count();
      $params['processed_count'] = $migration->getIdMap()->processedCount();
      $params['subject'] = $config->get('epartner_daily_report.subject');
      $params['body'] = $config->get('epartner_daily_report.body');

      $nodes = $this->getEPartners();
      if (!empty($nodes)) {
        $csv_file = $this->getCsvFile($nodes);
        if ($csv_file) {
          // Add attachments.
          $params['files'][] = $csv_file;
        }
      }

      $result = $this->mailManager->mail('athora_broker', 'athora_broker_epartner_daily_report', $to, $langcode, $params, NULL, TRUE);
      if ($result['result'] !== TRUE) {
        $this->logger->get('athora_broker')->error($this->t('There was a problem sending the daily report email to @email.', ['@email' => $to]));
        return;
      }

      $this->logger->get('athora_broker')->notice($this->t('An daily report email has been sent to @email.', ['@email' => $to]));
    }
  }

  /**
   * Get the csv file object.
   *
   * @param array $nodes
   *
   * @return object|null
   */
  private function getCsvFile(array $nodes) {
    $csv = NULL;
    try {
      $destination = file_directory_temp() . '/epartner-daily-report-' . $this->dateFormatter->format(time(), 'custom', 'dmY') . '.csv';
      if ($file_path = $this->fileSystem->saveData($this->getCsvContent($nodes), $destination)) {
        // Epartners attachment.
        $csv = new \stdClass();
        $csv->uri = $file_path;
        $csv->filename = 'epartner.csv';
        $csv->filemime = 'text/csv';
      }
    }
    catch (FileWriteException $e) {
      $this->logger->get('athora_broker')->error($this->t('The daily report file could not be created.'));
      return NULL;
    }

    return $csv;
  }

  /**
   * Get the CSV content.
   *
   * @param array $nodes
   *
   * @return bool|string
   */
  private function getCsvContent(array $nodes) {
    // Instead of writing down to a file we write to the memory stream.
    $fh = fopen('php://memory', 'r+');

    // Header.
    fputcsv($fh, [t('Broker ID'), t('URL')]);

    // Write data in the CSV format.
    foreach ($nodes as $node) {
      $broker_id = $node->get('field_broker_id')->value;
      $url = $node->toUrl('canonical', ['absolute' => TRUE])->toString();
      fputcsv($fh, [$broker_id, $url]);
    }

    // Close the stream.
    rewind($fh);
    $content = stream_get_contents($fh);
    fclose($fh);

    return $content;
  }

  /**
   * Get ePartners.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]|\Drupal\node\Entity\Node[]
   */
  private function getEPartners(): array {
    $nids = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('type', 'epartner')
      ->execute();

    return Node::loadMultiple($nids);
  }

}
