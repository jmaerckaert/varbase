<?php

namespace Drupal\athora_product\Plugin\ExtraField\Display;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\extra_field\Plugin\ExtraFieldDisplayBase;

/**
 * Document library quick link field.
 *
 * @ExtraFieldDisplay(
 *   id = "document_library_quick_link_field",
 *   label = @Translation("Document library Quick link"),
 *   bundles = {
 *     "node.product",
 *   },
 *   weight = 100,
 *   visible = true
 * )
 */
class DocumentLibraryQuickLinkField extends ExtraFieldDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity): array {
    $element = [];
    $documentsField = $this->getDocumentsField($entity);
    if ($documentsField && !$documentsField->isEmpty()) {
      // Build the field output as a concatenated string of document ids.
      $internal_documents = [];
      $external_documents = [];
      foreach ($documentsField as $item) {
        /** @var \Drupal\Core\Entity\ContentEntityInterface $document */
        $document = $item->entity;
        if ($document) {
          switch ($document->bundle()) {
            case 'file':
              $internal_documents[] = $document->id();
              break;
            case 'remote_file':
              $external_documents[] = $document->id();
              break;
          }
        }
      }
      $options = [];
      if (!empty($internal_documents) || !empty($external_documents)) {
        $link = [
          '#title' => t('Documents quick links'),
          '#type' => 'link',
          '#url' => Url::fromRoute('view.product_document_library.page_1', [
            'arg_0' => $entity->id(),
          ], $options)
        ];
        $element = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['document-link']
          ],
          'link' => [
            '#type' => 'html_tag',
            '#tag' => 'h3',
            '#value' => \Drupal::service('renderer')->renderRoot($link),
          ]
        ];
      }
    }

    return $element;
  }

  /**
   * Returns the Documents field this plugin uses.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|null
   *   The entities this field references.
   */
  protected function getDocumentsField(ContentEntityInterface $entity): ? FieldItemListInterface {
    $field = NULL;

    if ($entity->hasField('field_documents')) {
      $field = $entity->get('field_documents');
    }
    return $field;
  }

}
