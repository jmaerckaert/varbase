<?php

namespace Drupal\tbn_pagerer\Plugin\pagerer;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pagerer\Plugin\pagerer\PagererStyleBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Pager style to display a load more pager.
 *
 * @PagererStyle(
 *   id = "tbn_pagerer_load_more",
 *   title = @Translation("Load more pager"),
 *   short_title = @Translation("Load more"),
 *   help = @Translation("Pager style to display a load more pager."),
 *   style_type = "base"
 * )
 */
class LoadMore extends PagererStyleBase {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   *   The HTTP request object.
   */
  protected $request;

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TypedConfigManager $typed_config_manager, ConfigFactoryInterface $config_factory, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $typed_config_manager, $config_factory);
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.typed'),
      $container->get('config.factory'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }


  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = parent::buildConfigurationForm($form, $form_state);
    unset($config['links_container'], $config['separators_container']);

    return $config;
  }

  /**
   * Return the pager render array.
   *
   * @return array
   *   render array.
   */
  protected function buildPagerItems() {
    $state_settings = [];
    $items = [];
    $pagerer_widget_id = $this->prepareJsState($state_settings);

    $items[] = array(
      'widget' => array(
        '#theme' => 'tbn_pagerer_load_more',
        '#text' => $this->getDisplayTag('more_button_text'),
        '#attributes' => [
          'class' => ['use-ajax'],
          'id' => $pagerer_widget_id,
          'href' => $state_settings['url'] . '?' . str_replace('pagererpage', $state_settings['value'], $state_settings['queryString']),
          'title' => $this->getDisplayTag('more_button_text'),
        ],
        '#attached' => [
          'drupalSettings' => [
            'pagerer' => ['state' => [$pagerer_widget_id => $state_settings]],
          ],
        ],
      )
    );

    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function preprocess(array &$variables) {
    // Don't show the pager on the last page.
    if (($page = $this->request->query->get('page')) && (int) $this->pager->getTotalPages() === $page + 1) {
      return;
    }
    return parent::preprocess($variables);
  }

}
