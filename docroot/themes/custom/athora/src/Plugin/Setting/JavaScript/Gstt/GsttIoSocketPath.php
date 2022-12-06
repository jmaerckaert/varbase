<?php

namespace Drupal\athora\Plugin\Setting\JavaScript\Gstt;

use Drupal\bootstrap\Plugin\Setting\SettingBase;

/**
 * The "gstt_io_socket_path" theme setting.
 *
 * @ingroup plugins_setting
 *
 * @BootstrapSetting(
 *   id = "gstt_io_socket_path",
 *   type = "textfield",
 *   title = @Translation("Io Socket path"),
 *   description = @Translation("The io socket path options needed for Google speech to text."),
 *   defaultValue = "",
 *   groups = {
 *     "javascript" = @Translation("JavaScript"),
 *     "gstt" = @Translation("Google speech to text"),
 *     "options" = @Translation("Options"),
 *   },
 * )
 */
class GsttIoSocketPath extends SettingBase {

  /**
   * {@inheritdoc}
   */
  public function drupalSettings(): bool {
    return (bool)$this->theme->getSetting('gstt_io_socket_path');
  }

}
