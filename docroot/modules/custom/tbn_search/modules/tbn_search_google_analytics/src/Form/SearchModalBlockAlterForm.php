<?php

namespace Drupal\tbn_search_google_analytics\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Define SearchModalBlockAlterForm class.
 */
class SearchModalBlockAlterForm {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * SearchModalBlockAlterForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Alter search modal block form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param string $form_id
   */
  public function alterForm(&$form, FormStateInterface $form_state, $form_id): void {
    // Only run when site search tracking is enabled.
    if (!$this->configFactory->get('google_analytics.settings')->get('track.site_search')) {
      return;
    }

    $form['#attached']['library'][] = 'tbn_search_google_analytics/tbn-search-google-analytics';
    $form['#attached']['drupalSettings']['tbnSearchGoogleAnalytics'] = [
      'pagePath' => Url::fromRoute('search.view_google_site_search.modal')->toString(),
      'pageTitle' => t('Search') . ' | ' . $this->configFactory->get('system.site')->get('name'),
    ];
  }

}
