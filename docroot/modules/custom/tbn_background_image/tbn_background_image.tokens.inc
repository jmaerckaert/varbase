<?php

/**
 * @file
 * Token callbacks for the tbn_background_image module.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\background_image\BackgroundImageManager;

/**
 * Implements hook_token_info().
 */
function tbn_background_image_token_info() {
  $info = [];
  $background_image_manager = BackgroundImageManager::service();
  foreach ($background_image_manager->getEnabledEntityTypes() as $entity_type_id => $entity_type) {
    $token_type = \Drupal::service('token.entity_mapper')->getTokenTypeForEntityType($entity_type_id);
    if (!$token_type) {
      continue;
    }

    $info['tokens'][$token_type]['background_image'] = [
      'name' => t('Background image'),
      'description' => t('The background image of the @entity_type.', ['@entity_type' => $entity_type->getLabel()]),
      'type' => 'background_image',
    ];
  }

  return $info;
}

/**
 * Implements hook_tokens().
 */
function tbn_background_image_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $background_image_manager = BackgroundImageManager::service();
  $token_types = [];
  foreach ($background_image_manager->getEnabledEntityTypes() as $entity_type_id => $entity_type) {
    $token_type = \Drupal::service('token.entity_mapper')->getTokenTypeForEntityType($entity_type_id);
    if ($token_type) {
      $token_types[] = $token_type;
    }
  }
  $replacements = [];
  if (in_array($type, $token_types, FALSE) && !empty($data[$type])) {
    $background_image_manager = BackgroundImageManager::service();
    $token_service = \Drupal::token();
    if (($background_image_tokens = $token_service->findWithPrefix($tokens, 'background_image')) && $background_image = $background_image_manager->getEntityBackgroundImage($data[$type])) {
      $replacements += $token_service->generate('background_image', $background_image_tokens, ['background_image' => $background_image], $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
