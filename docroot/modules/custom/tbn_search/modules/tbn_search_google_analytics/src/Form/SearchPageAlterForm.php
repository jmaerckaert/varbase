<?php

namespace Drupal\tbn_search_google_analytics\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Define SearchPageAlterForm class.
 */
class SearchPageAlterForm {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * SearchPageAlterForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The stack of requests.
   */
  public function __construct(ConfigFactoryInterface $configFactory, RequestStack $request_stack) {
    $this->configFactory = $configFactory;
    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * Alter search page form.
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
    $wrapper_format = $this->currentRequest->get(MainContentViewSubscriber::WRAPPER_FORMAT);
    $is_modal = in_array($wrapper_format, ['drupal_modal', 'drupal_ajax']);
    if (!$is_modal) {
      return;
    }

    $form['#attached']['library'][] = 'tbn_search_google_analytics/tbn-search-google-analytics';
    $form['#attached']['drupalSettings']['tbnSearchGoogleAnalytics'] = [
      'pagePath' => Url::fromRoute('search.view_google_site_search.modal')->toString(),
      'pageTitle' => t('Search') . ' | ' . $this->configFactory->get('system.site')->get('name'),
    ];
  }
}
