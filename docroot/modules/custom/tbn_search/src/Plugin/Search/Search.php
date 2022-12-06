<?php

namespace Drupal\tbn_search\Plugin\Search;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\gss\Plugin\Search\Search as GssSearch;

/**
 * Handles search using Google Search Engine.
 */
class Search extends GssSearch {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config['add_referer_header'] = 0;
    $config['referer_header'] = '';
    return $config;
  }


  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['add_referer_header'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Referer header'),
      '#description' => $this->t('Add the referer header to the google API request.'),
      '#default_value' => $this->configuration['add_referer_header'],
    );
    $form['referer_header'] = array(
      '#type' => 'textfield',
      '#default_value' => $this->configuration['referer_header'],
      '#states' => [
        'visible' => [
          ':input[name="add_referer_header"]' => ['checked' => TRUE],
        ]
      ]
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $defaults = array_keys($this->defaultConfiguration());
    foreach ($defaults as $key) {
      $this->configuration[$key] = $form_state->getValue($key);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getResults($n = 1, $offset = 0, $search_type = NULL) {
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $api_key = $this->keyRepository->getKey($this->configuration['api_key']);
    $query = $this->getParameters();
    $search_engine_id = $this->configuration['search_engine_id_' . $language];

    $options = [
      'headers' => [],
      'query' => [
        'q' => $this->keywords,
        'key' => $api_key->getKeyValue(),
        'cx' => $search_engine_id,
        // hl: "interface language", also used to weight results.
        'hl' => $language,
        'start' => $offset + 1,
        'num' => $n,
      ],
    ];

    if (@$query['type'] === 'image') {
      $options['query']['searchType'] = 'image';
    }

    if ($this->configuration['add_referer_header']) {
      $options['headers']['Referer'] = $this->configuration['referer_header'] ?? \Drupal::request()->server->get('HTTP_REFERER');
    }

    try {
      $response = $this
        ->httpClient
        ->get($this->configuration['base_url'], $options);
    } catch (\Exception $e) {
      watchdog_exception('gss', $e);
      return NULL;
    }
    return json_decode($response->getBody());
  }

  /**
   * {@inheritdoc}
   */
  public function suggestedTitle() {
    if (!empty($this->keywords)) {
      return $this->t("Results for <span class='search-result-text'>'@keywords'</span>", ['@keywords' => Unicode::truncate($this->keywords, 60, TRUE, TRUE)]);
    }

    return $this->t('Search');
  }


}

