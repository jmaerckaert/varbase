<?php

namespace Drupal\athora\Plugin\Setting\JavaScript\Gstt;

use Drupal\bootstrap\Plugin\Setting\SettingBase;

/**
 * The "gstt_io_socket_url" theme setting.
 *
 * @ingroup plugins_setting
 *
 * @BootstrapSetting(
 *   id = "gstt_io_socket_url",
 *   type = "textfield",
 *   title = @Translation("Io Socket url"),
 *   description = @Translation("The io socket url needed for Google speech to text."),
 *   defaultValue = "",
 *   groups = {
 *     "javascript" = @Translation("JavaScript"),
 *     "gstt" = @Translation("Google speech to text"),
 *   },
 * )
 */
class GsttIoSocketUrl extends SettingBase {

  /**
   * {@inheritdoc}
   */
  public function drupalSettings(): bool {
    return (bool)$this->theme->getSetting('gstt_io_socket_url');
  }

}
