<?php

namespace Drupal\athora_heroslider_media\Plugin\paragraphs\Behavior;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\File\FileUrlGenerator;
use Drupal\Core\Template\Attribute;
use Drupal\file\Entity\File;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\paragraphs\ParagraphsBehaviorBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a media slide picture plugin.
 *
 * @ParagraphsBehavior(
 *   id = "media_slide_picture",
 *   label = @Translation("Media slide picture plugin"),
 *   description = @Translation("Media slide picture plugin"),
 *   weight = 2
 * )
 */
class MediaSlidePictureBehavior extends ParagraphsBehaviorBase {
  /**
   * The File Generator URL.
   *
   * @var \Drupal\Core\File\FileUrlGenerator
   */
  protected FileUrlGenerator $fileUrlGenerator;

  /**
   * Constructs a ParagraphsBehaviorBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\File\FileUrlGenerator $file_url_generator
   *   The URL file generator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFieldManagerInterface $entity_field_manager, FileUrlGenerator $file_url_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_field_manager);
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('entity_field.manager'),
      $container->get('file_url_generator'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(array &$build, Paragraph $paragraphs_entity, EntityViewDisplayInterface $display, $view_mode) {
    return;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(ParagraphsType $paragraphs_type) {
    return ($paragraphs_type->id() === 'slide_media');
  }

  /**
   * {@inheritdoc}
   */
  public function preprocess(&$variables) {
    parent::preprocess($variables);

    $variables['img_element'] = [];
    $variables['sources'] = [];

    // Get the target id and build the url.
    $paragraph = &$variables['paragraph'];
    if (isset($paragraph->field_slide_media_web, $paragraph->field_slide_media_web->target_id) && $target_id = $paragraph->field_slide_media_web->entity->field_media_image->target_id) {
      $file = File::load($target_id);
      if (isset($file)) {
        $source_attributes = new Attribute([
            'srcset' => $this->fileUrlGenerator->transformRelative($this->fileUrlGenerator->generateAbsoluteString($file->getFileUri())),
          'media' => '(min-width: 480px)'
        ]);
        $variables['sources'][] = $source_attributes;
        $variables['img_element'] = [
          '#theme' => 'image',
          '#uri' => $file->getFileUri(),
        ];
      }
    }
    if (isset($paragraph->field_slide_media_mobile, $paragraph->field_slide_media_mobile->target_id) && $target_id = $paragraph->field_slide_media_mobile->entity->field_media_image->target_id) {
      $file = File::load($target_id);
      if (isset($file)) {
        $source_attributes = new Attribute([
          'srcset' => $this->fileUrlGenerator->transformRelative($this->fileUrlGenerator->generateAbsoluteString($file->getFileUri())),
          'media' => '(max-width: 479px)'
        ]);
        $variables['sources'][] = $source_attributes;
      }
    }
  }

}
