<?php

namespace Drupal\tbn_search\Routing;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\gss\Plugin\Search\Search as GssSearch;
use Drupal\search\SearchPageRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides dynamic results routes for search and alter existing search routes.
 */
class SearchPageRoutes extends RouteSubscriberBase implements ContainerInjectionInterface {

  /**
   * The search page repository.
   *
   * @var \Drupal\search\SearchPageRepositoryInterface
   */
  protected $searchPageRepository;

  /**
   * Constructs a new search route subscriber.
   *
   * @param \Drupal\search\SearchPageRepositoryInterface $search_page_repository
   *   The search page repository.
   */
  public function __construct(SearchPageRepositoryInterface $search_page_repository) {
    $this->searchPageRepository = $search_page_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('search.search_page_repository')
    );
  }

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes(): array {
    $routes = [];

    $active_pages = $this->searchPageRepository->getActiveSearchPages();
    foreach ($active_pages as $entity_id => $entity) {
      $routes["search.view_$entity_id.modal"] = new Route(
        '/search/' . $entity->getPath() . '/modal',
        [
          '_controller' => 'Drupal\tbn_search\Controller\GssSearchController::modal',
          '_title' => 'Search modal',
          'entity' => $entity_id,
        ],
        [
          '_custom_access' => 'Drupal\tbn_search\Controller\GssSearchController::access',
          '_entity_access' => 'entity.view',
          '_permission' => 'search content',
        ],
        [
          'parameters' => [
            'entity' => [
              'type' => 'entity:search_page',
            ],
          ],
        ]
      );
      $routes["search.view_$entity_id.results"] = new Route(
        '/search/' . $entity->getPath() . '/results',
        [
          '_controller' => 'Drupal\tbn_search\Controller\GssSearchController::ajaxSearchResults',
          '_title' => 'Search results',
          'entity' => $entity_id,
        ],
        [
          '_custom_access' => 'Drupal\tbn_search\Controller\GssSearchController::access',
          '_entity_access' => 'entity.view',
          '_permission' => 'search content',
        ],
        [
          'parameters' => [
            'entity' => [
              'type' => 'entity:search_page',
            ],
          ],
        ]
      );
      $routes["search.view_$entity_id.results_load_more"] = new Route(
        '/search/' . $entity->getPath() . '/results/load-more',
        [
          '_controller' => 'Drupal\tbn_search\Controller\GssSearchController::ajaxSearchResultsLoadMore',
          '_title' => 'Search results load more',
          'entity' => $entity_id,
        ],
        [
          '_custom_access' => 'Drupal\tbn_search\Controller\GssSearchController::access',
          '_entity_access' => 'entity.view',
          '_permission' => 'search content',
        ],
        [
          'parameters' => [
            'entity' => [
              'type' => 'entity:search_page',
            ],
          ],
        ]
      );
    }

    return $routes;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    foreach ($this->searchPageRepository->getActiveSearchPages() as $entity_id => $entity) {
      if ($entity->getPlugin() instanceof GssSearch &&
        $route = $collection->get("search.view_$entity_id")
      ) {
        // Denied access to the default route.
        $route->setRequirement('_access', 'FALSE');
        $route->setDefault('_controller',
          'Drupal\tbn_search\Controller\GssSearchController::view');
      }
    }
  }

}
