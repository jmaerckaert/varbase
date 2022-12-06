<?php

namespace Drupal\tbn_search\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tbn_search\Controller\GssSearchController;

/**
 * Class SearchPageAlterForm.
 */
class SearchPageAlterForm {

  /**
   * Alter search page form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param string $form_id
   */
  public function alterForm(&$form, FormStateInterface $form_state, $form_id): void {
    if ($this->isModal()) {
      // Attach the library for pop-up dialogs/modals.
      $form['#attached']['library'][] = 'core/drupal.ajax';
      $form['#attached']['library'][] = 'core/jquery.form';
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

      $form['basic']['keys']['#title'] = t('Type your search here...');
      $form['basic']['keys']['#description'] = FALSE;
      $form['basic']['keys']['#icon_only'] = FALSE;
      $form['basic']['keys']['#attributes']['class'][] = 'delayed-input-submit';
      $form['basic']['keys']['#ajax'] = [
        'callback' => [$this, 'submitModalFormAjax'],
        'event' => 'delayed_input',
        'progress' => 'none',
      ];
      $form['#submit'] = [];
      unset($form['basic']['submit']);
      if (\Drupal::currentUser()->hasPermission('use advanced search')) {
        $form['advanced']['submit']['#ajax'] = [
          'callback' => [$this, 'submitModalFormAjax'],
        ];
      }
      $form['basic']['speech'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => '<span class="path1"></span><span class="path2"></span>',
        '#attributes' => [
          'class' => ['icon-modal-gstt', 'stt-microphone', 'speech-search'],
          'aria-hidden' => 'true',
        ],
      ];
      $form['#attached']['library'][] = 'tbn_search/tbn-search';
      unset($form['help_link']);
    }
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state): AjaxResponse {
    $search_page_repository = \Drupal::service('search.search_page_repository');
    $renderer = \Drupal::service('renderer');
    $form_builder = \Drupal::service('form_builder');
    $request = \Drupal::request();
    $controller = new GssSearchController($search_page_repository, $renderer, $form_builder, $request);
    /** @var \Drupal\search\SearchPageInterface $entity */
    $search_page = $form_state->getBuildInfo()['args'][0] ?? NULL;
    if ($search_page) {
      $query = $search_page->getPlugin()->buildSearchUrlQuery($form_state);
      $request->query->add($query);
    }
    return $controller->ajaxSearchResults($request, $search_page);
  }

  /**
   * Determine if this form is being displayed in a modal dialog.
   *
   * @return bool
   *   TRUE is the form is being display in a modal dialog.
   */
  protected function isModal(): bool {
    $wrapper_format = \Drupal::request()->get(MainContentViewSubscriber::WRAPPER_FORMAT);
    return in_array($wrapper_format, ['drupal_modal', 'drupal_ajax']);
  }
}