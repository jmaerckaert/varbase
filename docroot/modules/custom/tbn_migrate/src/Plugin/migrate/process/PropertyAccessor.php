<?php

namespace Drupal\tbn_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Gets the source value.
 *
 * @MigrateProcessPlugin(
 *   id = "property_accessor"
 * )
 */
class PropertyAccessor extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $propertyAccessor = PropertyAccess::createPropertyAccessor();
    $property_path = $this->configuration['property_path'] ?? NULL;
    if ($property_path) {
      return $propertyAccessor->getValue($value, $property_path);
    }

    return $value;
  }

}
