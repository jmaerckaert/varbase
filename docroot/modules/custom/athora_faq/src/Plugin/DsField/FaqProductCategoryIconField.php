<?php

namespace Drupal\athora_faq\Plugin\DsField;

use Drupal\Core\Url;
use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Plugin that renders the name of the product category linked to the FAQ list.
 *
 * @DsField(
 *   id = "faq_product_category_icon_field",
 *   title = @Translation("FAQ Product category icon"),
 *   entity_type = "taxonomy_term"
 * )
 */
class FaqProductCategoryIconField extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function isAllowed(): bool {
    return $this->bundle() === 'product_category' && $this->viewMode() === 'faq';
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity = $this->entity();

    return array(
      '#theme' => 'image_formatter',
      '#item' => $entity->get('field_icon')->referencedEntities()[0]->get('field_media_image')[0],
      '#item_attributes' => [],
      '#url' => Url::fromUri("internal:/faq/{$entity->id()}"),
    );
  }
}
