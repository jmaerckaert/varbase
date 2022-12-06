<?php

namespace Drupal\athora\Plugin\Preprocess;

use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\bootstrap\Utility\Variables;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Pre-processes variables for the "views_view__document_library__page_1"
 * theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("views_view_list__document_library__page_1")
 */
class ViewsViewDocumentLibraryPage extends PreprocessBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, Request $request, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $request;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('language_manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables) {
    $variables['#attached']['library'][]= 'athora/accordion-script';
    $rows = $variables['rows'];
    if (empty($rows)) {
      return;
    }

    $grouped_rows = [];
    foreach ($rows as $row) {
      /** @var \Drupal\views\ResultRow $result_row */
      $result_row = $row['content']['#row'];
      /** @var \Drupal\taxonomy\Entity\Term $document_category */
      $document_category = $result_row->_relationship_entities['field_media_document_category'] ?? NULL;
      if ($document_category === NULL) {
        continue;
      }

      $parents = $this->entityTypeManager
        ->getStorage('taxonomy_term')
        ->loadAllParents($document_category->id());
      $result = $this->buildingHierarchicalArray($parents, $row, $result_row->_entity->id());
      $grouped_rows = NestedArray::mergeDeepArray([
        $grouped_rows,
        $result,
      ], TRUE);
    }

    // Sort values by weight.
    $this->sortRecursive($grouped_rows);

    // Set the active trail.
    $target = $this->request->query->get('target');
    if ($target !== NULL) {
      $parents = $this->entityTypeManager
        ->getStorage('taxonomy_term')
        ->loadAllParents($target);
      if (!empty($parents)) {
        $active_trail_result = $this->setActiveTrail(array_keys($parents));
        $grouped_rows = NestedArray::mergeDeepArray([
          $grouped_rows,
          $active_trail_result,
        ], TRUE);
      }
    }
    $variables['rows'] = $grouped_rows;
  }

  /**
   * Sort sub accordion items recursively.
   *
   * @param array $array
   *
   * @return void
   */
  public function sortRecursive(array &$array): void {
    uasort($array, [SortArray::class, 'sortByWeightElement']);
    foreach ($array as &$item) {
      if (!isset($item['accordion'])) {
        continue;
      }
      uasort($item['accordion'], [SortArray::class, 'sortByWeightElement']);
      $this->sortRecursive($item['accordion']);
    }
  }

  /**
   * Build hierarchical array structure.
   *
   * @param array $parents
   *   The parents of the document term.
   * @param array $row
   *   The build array.
   * @param int $mid
   *   The media entity id.
   *
   * @return array
   *   The hierarchical array of the documents.
   */
  public function buildingHierarchicalArray(array $parents, array $row, int $mid): array {
    if (!count($parents)) {
      return $row;
    }
    $dynamic_associative_array = [];
    $i = 0;
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    foreach ($parents as $term) {
      if ($term->hasTranslation($langcode)) {
        $term = $term->getTranslation($langcode);
      }
      $dynamic_associative_array = [
        $term->id() => [
          'term' => $term,
          'weight' => $term->getWeight(),
          'active' => FALSE,
        ],
      ];
      if ($i > 0) {
        $dynamic_associative_array[$term->id()]['accordion'] = $row;
      }
      else {
        $dynamic_associative_array[$term->id()]['children'] = [$mid => $row];
      }
      $row = $dynamic_associative_array;
      $i++;
    }

    return $dynamic_associative_array;
  }

  /**
   * Set active trail.
   *
   * @param array $parents
   *   The parents of the target term.
   * @param array $build
   *   The active trail array.
   *
   * @return array
   *   The hierarchical structure of the active trail.
   */
  private function setActiveTrail(array $parents, array $build = []): array {
    $dynamic_associative_array = [];
    $item = array_shift($parents);
    if (!empty($parents)) {
      $sub_build = [
        $item => [
          'active' => TRUE,
        ]
      ];
      if (!empty($build)) {
        $sub_build[$item]['accordion'] = $build;
      }
      $dynamic_associative_array += $this->setActiveTrail($parents, $sub_build);
    }
    else {
      $dynamic_associative_array[$item] = [
        'active' => TRUE,
        'accordion' => $build
      ];
    }

    return $dynamic_associative_array;
  }

}
