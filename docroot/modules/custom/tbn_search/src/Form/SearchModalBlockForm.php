<?php

namespace Drupal\tbn_search\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\search\SearchPageRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define SearchModalBlockForm class.
 */
class SearchModalBlockForm extends FormBase {

  /**
   * The search page repository.
   *
   * @var \Drupal\search\SearchPageRepositoryInterface
   */
  protected $searchPageRepository;

  /**
   * Constructs a new SearchBlockForm.
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
  public function getFormId() {
    return 'search_modal_block_form';
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('search.search_page_repository'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $config = []): array {
    $entity_id = $this->searchPageRepository->getDefaultSearchPage();
    if ($entity_id) {
      $active_search_pages = $this->searchPageRepository->getActiveSearchPages();
      $form['#theme_wrappers'] = [
        'container' => [
          '#attributes' => ['class' => 'search-modal-block-form'],
        ],
      ];
      $form['keys'] = [
        '#type' => 'search',
        '#title' => $this->t('Type your search here...'),
        '#title_display' => 'invisible',
        '#size' => 15,
        '#default_value' => '',
        '#attributes' => [
          'title' => $this->t('Type your search here...'),
          'placeholder' => $this->t('Type your search here...'),
          'class' => [
            'search-modal-input'
          ],
        ],
      ];
      $form['open_modal'] = [
        '#type' => 'link',
        '#title' => $this->t(''),
        '#url' => Url::fromRoute("search.view_$entity_id.modal", ['entity' => $active_search_pages[$entity_id]]),
        '#attributes' => [
          'class' => [
            'search-use-ajax',
            'glyphicon',
            'glyphicon-search',
            'btn-modal-search',
          ],
          'id' => Html::getUniqueId('ajax-search'),
        ],
      ];
      $form['speech'] = [
        '#type' => 'link',
        '#title' => ['#markup' => '<span class="path1"></span><span class="path2"></span>'],
        '#url' => Url::fromRoute("search.view_$entity_id.modal", ['entity' => $active_search_pages[$entity_id]]),
        '#attributes' => [
          'class' => [
            'stt-microphone',
            'speech-search',
          ],
        ],
        // Only show the speech icon element when this is enabled.
        '#access' => $config['show_speech_icon'] ? (bool) $config['show_speech_icon'] : FALSE,
      ];
      // Attach the library for pop-up dialogs/modals.
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
      $form['#attached']['library'][] = 'tbn_search/tbn-search';
      $form['#attached']['library'][] = 'athora/socket.io-client';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {}

}
