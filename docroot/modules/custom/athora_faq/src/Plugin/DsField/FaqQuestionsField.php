<?php

namespace Drupal\athora_faq\Plugin\DsField;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Plugin that renders the name of the product category linked to the FAQ list.
 *
 * @DsField(
 *   id = "faq_questions_field",
 *   title = @Translation("FAQ Questions"),
 *   entity_type = "block_content"
 * )
 */
class FaqQuestionsField extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function isAllowed(): bool {
    return $this->bundle() === 'faq_block' && $this->viewMode() === 'default';
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity = $this->entity();
    if (!isset($entity->get('field_product_category')->referencedEntities()[0])) {
      return [];
    }
    $product_category_id = $entity->get('field_product_category')->referencedEntities()[0]->id();

    $items = [];
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    foreach ($entity->get('field_faq_questions')->referencedEntities() as $question) {
      $title = $question->getTitle();
      if ($question->hasTranslation($langcode)) {
        // If the entity has translation, fetch the translated value.
        $title = $question->getTranslation($langcode)->getTitle();
      }
      if ($question->hasField('field_faq_category') && !$question->get('field_faq_category')->isEmpty()) {
        $items[] = Link::fromTextAndUrl($title, Url::fromUri("internal:/faq/{$product_category_id}#faq-{$question->id()}-{$question->get('field_faq_category')->target_id}"));
      }
      else {
        $items[] = Link::fromTextAndUrl($title, Url::fromUri("internal:/faq/{$product_category_id}#faq-{$question->id()}"));
      }
    }

    return array(
      '#theme' => 'item_list',
      '#items' => $items,
    );
  }
}
