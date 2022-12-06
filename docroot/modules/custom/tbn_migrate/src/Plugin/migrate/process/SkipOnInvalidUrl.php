<?php

namespace Drupal\tbn_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use \Drupal\migrate_process_url\Plugin\migrate\process\SkipOnInvalidUrl as SkipOnInvalidUrlBase;

/**
 * {@inheritDoc}
 *
 * Add configuration to log process messages.
 */
class SkipOnInvalidUrl extends SkipOnInvalidUrlBase {

  /**
   * {@inheritdoc}
   */
  public function process($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    try {
      parent::process($value, $migrate_executable, $row, $destination_property);
    }
    catch (MigrateSkipProcessException $e) {
      $message = t($e->getMessage(), ['@value' => $value]);
      $migrate_executable->saveMessage($message);
      throw new MigrateSkipProcessException($message);
    }

    return $value;
  }

  /**
   * Check the URL for validity.
   *
   * @param $uri
   *  The url to check.
   *
   * @return bool
   *   TRUE if the URL is valid, FALSE otherwise.
   */
  protected function isValid($uri) {
    // Empty values cannot be valid URLs.
    $skip_empty_url_valid_check = $this->configuration['skip_empty_url_valid_check'] ?? FALSE;
    if ($skip_empty_url_valid_check && empty($uri)) {
      return TRUE;
    }

    if (empty($uri)) {
      return FALSE;
    }

    // Allow us to check absolute URLs if so configured.
    $abs = empty($this->configuration['absolute']) ? FALSE : $this->configuration['absolute'];

    // Check the URL.
    return $this->isUrlValid($uri, $abs);
  }

  /**
   * Verifies the syntax of the given URL.
   *
   * We have added the UNICODE modifier extra.
   *
   * @param string $url
   *   The URL to verify.
   * @param bool $absolute
   *   Whether the URL is absolute (beginning with a scheme such as "http:").
   *
   * @return bool
   *   TRUE if the URL is in a valid format, FALSE otherwise.
   *
   * @see \Drupal\Component\Utility\UrlHelper::isValid()
   */
  private function isUrlValid($url, $absolute = FALSE): bool {
    if ($absolute) {
      return (bool) preg_match("
        /^                                                      # Start at the beginning of the text
        (?:ftp|https?|feed):\/\/                                # Look for ftp, http, https or feed schemes
        (?:                                                     # Userinfo (optional) which is typically
          (?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*      # a username or a username and password
          (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@          # combination
        )?
        (?:
          (?:[a-z0-9\-\.]|%[0-9a-f]{2})+                        # A domain name or a IPv4 address
          |(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\])         # or a well formed IPv6 address
        )
        (?::[0-9]+)?                                            # Server port number (optional)
        (?:[\/|\?]
          (?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})   # The path and query (optional)
        *)?
      $/xiu", $url);
    }
    else {
      return (bool) preg_match("/^(?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})+$/i", $url);
    }
  }

}
