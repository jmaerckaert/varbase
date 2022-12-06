<?php

namespace Drupal\athora_faq\Plugin\DsField;

use Drupal\Core\Url;
use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Plugin that renders the name of the product category linked to the FAQ list.
 *
 * @DsField(
 *   id = "faq_product_category_name_field",
 *   title = @Translation("FAQ Product category name"),
 *   entity_type = "taxonomy_term"
 * )
 */
class FaqProductCategoryNameField extends DsFieldBase {

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
      '#title' => $entity->getName(),
      '#type' => 'link',
      '#url' => Url::fromUri("internal:/faq/{$entity->id()}"),
    );
  }
}
