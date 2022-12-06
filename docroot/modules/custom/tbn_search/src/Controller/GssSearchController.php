<?php

namespace Drupal\tbn_search\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\gss\Plugin\Search\Search;
use Drupal\search\Controller\SearchController;
use Drupal\search\Form\SearchPageForm;
use Drupal\search\SearchPageInterface;
use Drupal\search\SearchPageRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Access\AccessResultInterface;

/**
 * Route controller for GSS Search.
 */
class GssSearchController extends SearchController {

  use AjaxHelperTrait;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   */
  public function __construct(SearchPageRepositoryInterface $search_page_repository, RendererInterface $renderer, FormBuilderInterface $form_builder, Request $request) {
    parent::__construct($search_page_repository, $renderer);
    $this->formBuilder = $form_builder;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('search.search_page_repository'),
      $container->get('renderer'),
      $container->get('form_builder'),
      $container->get('request_stack')->getCurrentRequest(),
      );
  }

  /**
   * Search modal.
   *
   * @param \Drupal\search\SearchPageInterface $entity
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function modal(SearchPageInterface $entity): AjaxResponse {
    $response = new AjaxResponse();

    // Get the modal form using the form builder.
    $plugin = $entity->getPlugin();
    if ($this->request->request->has('keys')) {
      $keys = trim($this->request->request->get('keys'));
      $plugin->setSearch($keys, [], $this->request->attributes->all());
    }
    $form = $this->formBuilder->getForm(SearchPageForm::class, $entity);
    $form['search_results'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['search-results-wrapper']
      ],
      'dummy' => [
        '#markup' => $this->t('Results will be listed here'),
      ]
    ];
    $response->setAttachments($form['#attached']);
    // Add an AJAX command to open a modal dialog with the form as the content.
    $margin_top = 20;
    if (\Drupal::currentUser()->hasPermission('access toolbar')) {
      $margin_top = 100;
    }
    $response->addCommand(new OpenModalDialogCommand('', $form, ['width' => '80%', 'position' => ['my' => 'center top', 'at' => "center top+$margin_top"]]));

    return $response;
  }

  /**
   * Get plugin results for the ajax callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\search\SearchPageInterface $entity
   *
   * @return AjaxResponse
   */
  public function ajaxSearchResults(Request $request, SearchPageInterface $entity): AjaxResponse {
    $response = new AjaxResponse();

    // If there are any form errors, re-display the form.
    $build = $this->getBuild($request, $entity);
    $response->addCommand(new ReplaceCommand('#search-results-wrapper', $this->renderer->renderRoot($build)));

    $status_messages = ['#type' => 'status_messages'];
    $output = $this->renderer->renderRoot($status_messages);
    if (!empty($output)) {
      $response->addCommand(new PrependCommand('#search-results-wrapper', $output));
    }
    $response->setAttachments($build['#attached']);

    return $response;
  }

  /**
   * Get plugin results for the ajax load more callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\search\SearchPageInterface $entity
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function ajaxSearchResultsLoadMore(Request $request, SearchPageInterface $entity): AjaxResponse {
    $response = new AjaxResponse();

    $build = $this->getBuild($request, $entity, FALSE);
    $pager = $build['pager'];
    unset($build['search_for'], $build['pager']);
    $response->addCommand(new AfterCommand('#search-results-wrapper .item-list', $this->renderer->renderRoot($build)));
    $response->addCommand(new ReplaceCommand('#search-results-wrapper .pagerer-container', $this->renderer->renderRoot($pager)));

    $status_messages = ['#type' => 'status_messages'];
    $output = $this->renderer->renderRoot($status_messages);
    if (!empty($output)) {
      $response->addCommand(new PrependCommand('#search-results-wrapper', $output));
    }
    $response->setAttachments($build['#attached']);

    return $response;
  }

  /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account): AccessResultInterface {
    return $this->isAjax() ? AccessResult::allowed() : AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  public function view(Request $request, SearchPageInterface $entity) {
    /** @var \Drupal\gss\Plugin\Search\Search $plugin */
    $plugin = $entity->getPlugin();

    $build = parent::view($request, $entity);

    $build['pager'] = [
      '#type' => 'pager',
      '#theme' => 'pagerer',
      '#element' => 0,
      '#route_name' => '<current>',
      '#quantity' => $plugin->getPagerSize(),
      '#config' => []
    ];

    return [
      '#cache' => $build['#cache'],
      '#title' => $build['#title'],
      'search_form' => $build['search_form'],
      'search_results_title' => @$build['search_results_title'],
      'links' => $plugin->getSearchOptions($request),
      'search_results' => $build['search_results'],
      'pager' => $build['pager'],
    ];
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\search\SearchPageInterface $entity
   * @param \Drupal\gss\Plugin\Search\Search $plugin
   * @param array $build
   *
   * @return array
   */
  private function getSearchResults(Request $request, SearchPageInterface $entity, Search $plugin, array $build): array {
    // Build search results, if keywords or other search parameters are in the
    // GET/POST parameters. Note that we need to try the search if 'keys' is in
    // there at all, vs. being empty, due to advanced search.
    $results = [];
    $keys = $this->getKeys($request, 'request');
    if (!empty($keys)) {
      $plugin->setSearch($keys, $request->request->all(), $request->attributes->all());
      $results = $this->getResults($entity, $plugin, $keys);
    }
    if (empty($results) && ($keys = $this->getKeys($request, 'query')) && !empty($keys)) {
      $plugin->setSearch($keys, $request->query->all(), $request->attributes->all());
      $results = $this->getResults($entity, $plugin, $keys);
    }

    if (empty($keys)) {
      // The search not being executable means that no keywords or other
      // conditions were entered.
      $this->messenger()->addError($this->t('Please enter some keywords.'));
    }

    if (!empty($keys)) {
      $build['search_for'] = [
        '#markup' => $plugin->suggestedTitle(),
        '#prefix' => '<span class="search-result-suggestion">',
        '#suffix' => '</span>',
      ];
    }

    $build['search_results'] = [
      '#theme' => [
        'item_list__search_results__' . $plugin->getPluginId(),
        'item_list__search_results'
      ],
      '#items' => $results,
      '#empty' => [
        '#markup' => '<h3>' . $this->t('Your search yielded no results.') . '</h3>',
      ],
      '#list_type' => 'ol',
      '#context' => [
        'plugin' => $plugin->getPluginId(),
      ],
    ];
    return $build;
  }

  /**
   * Get keys.
   *
   * @param Request $request
   * @param string $param_bag_name
   *
   * @return string
   */
  private function getKeys($request, $param_bag_name): string {
    $keys = '';
    if ($request->{$param_bag_name}->has('keys') && !empty($request->{$param_bag_name}->get('keys'))) {
      $keys = trim($request->{$param_bag_name}->get('keys'));
    }

    return $keys;
  }

  /**
   * Get plugin results.
   *
   * @param \Drupal\search\SearchPageInterface $entity
   * @param \Drupal\gss\Plugin\Search\Search $plugin
   * @param string $keys
   *
   * @return array
   */
  private function getResults(SearchPageInterface $entity, Search $plugin, string $keys): array {
    $results = [];
    if ($plugin->isSearchExecutable()) {
      // Log the search.
      if ($this->config('search.settings')->get('logging')) {
        $this->logger->notice('Searched %type for %keys.', [
          '%keys' => $keys,
          '%type' => $entity->label()
        ]);
      }

      // Collect the search results.
      $results = $plugin->buildResults();
    }
    return $results;
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\search\SearchPageInterface $entity
   * @param bool $add_wrapper
   *
   * @return array
   */
  private function getBuild(Request $request, SearchPageInterface $entity, $add_wrapper = TRUE): array {
    /** @var \Drupal\gss\Plugin\Search\Search $plugin */
    $plugin = $entity->getPlugin();

    $build = [
      '#cache' => [
        'contexts' => [
          'url.query_args:keys'
        ]
      ]
    ];

    $build = $this->getSearchResults($request, $entity, $plugin, $build);
    if ($add_wrapper) {
      $build['#theme_wrappers'] = ['container'];
      $build['#attributes'] = ['id' => ['search-results-wrapper']];
    }

    $entity_id = $entity->id();
    $build['pager'] = [
      '#type' => 'pager',
      '#theme' => 'pagerer',
      '#element' => 0,
      '#route_name' => "search.view_$entity_id.results_load_more",
      '#config' => [
        'preset' => 'load_more',
      ],
      '#quantity' => $plugin->getPagerSize()
    ];

    $this->renderer->addCacheableDependency($build, $entity);
    if ($plugin instanceof CacheableDependencyInterface) {
      $this->renderer->addCacheableDependency($build, $plugin);
    }

    // If this plugin uses a search index, then also add the cache tag tracking
    // that search index, so that cached search result pages are invalidated
    // when necessary.
    if ($plugin->getType()) {
      $build['search_results']['#cache']['tags'][] = 'search_index';
      $build['search_results']['#cache']['tags'][] = 'search_index:' . $plugin->getType();
    }

    return $build;
  }

}
