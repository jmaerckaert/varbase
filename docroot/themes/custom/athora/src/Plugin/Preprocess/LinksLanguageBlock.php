<?php

namespace Drupal\athora\Plugin\Preprocess;

use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\bootstrap\Utility\Variables;
use Drupal\Core\Language\LanguageInterface;

/**
 * Pre-processes variables for the "links" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("links__language_block")
 */
class LinksLanguageBlock extends PreprocessBase {

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables) {
    $variables['current_language'] = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();

    // Remove the language interface langcode link.
    /** @var \Drupal\Core\Config\ConfigFactory $config */
    $config = \Drupal::configFactory()->get('admin_user_language.settings');
    $default_language = $config->get('default_language_to_assign');
    $preferred_admin_langcode = (!\Drupal::currentUser()->isAnonymous()) ? \Drupal::currentUser()->getPreferredAdminLangcode() : $default_language;
    $links =& $variables['links'] ?? [];
    if (isset($links[$preferred_admin_langcode])) {
      unset($links[$preferred_admin_langcode]);
    }
  }

}
