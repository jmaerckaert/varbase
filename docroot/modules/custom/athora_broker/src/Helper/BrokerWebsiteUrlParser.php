<?php

namespace Drupal\athora_broker\Helper;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Url;

/**
 * Defines a helper class concerning website URL's for brokers.
 */
class BrokerWebsiteUrlParser {

  /**
   * @var EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * @param EntityTypeManager $entityTypeManager
   */
  public function setEntityTypeManager(EntityTypeManager $entityTypeManager): void {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Prefix schemeless URL's with http:// because that will most likely forward
   * to the https:// variant if there is one. Using // is much more dangerous
   * because that would prefix every URL with https:// which can cause some
   * URL's to become unreachable
   *
   * @param string $url
   *
   * @return string|null
   */
  public function parseWebsiteUrl(string $url): ?string {
    $website = NULL;
    if (!empty($url)) {
      $uri_parts = parse_url($url);
      if (isset($uri_parts['scheme'])) {
        $website = $url;
      }
      else {
        $website = 'http://' . $url;
      }
    }

    return $website;
  }

  /**
   * Generate a link to the e-partner page when no website is found for the broker.
   *
   * @param array $brokers
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function retrievePartnerPages(array $brokers): array {
    $epartner_pages = [];

    foreach ($brokers as $broker) {
      if (!$broker->getWebsiteURL()) {
        $epartner_pages[$broker->getBroker()->getUserNumber()] = '';
      }
    }

    if (empty($epartner_pages)) {
      return $epartner_pages;
    }

    $entity_storage = $this->entityTypeManager->getStorage('node');
    $query_result = $entity_storage->getQuery()
      ->condition('type', 'epartner')
      ->condition('status', '1')
      ->condition('field_broker_id', array_keys($epartner_pages), 'IN')
      ->accessCheck(FALSE)
      ->execute();

    if (!empty($query_result) && !empty($entities = $entity_storage->loadMultiple($query_result))) {
      foreach ($entities as $id => $node) {
        $epartner_pages[$node->get('field_broker_id')->getValue()[0]['value']] = Url::fromUri("internal:/node/{$id}")->toString();
      }
    }

    return $epartner_pages;
  }
}
