<?php

namespace Drupal\athora_broker\EventSubscriber;

use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class Subscriber.
 */
class EPartnerLanguageAccessSubscriber implements EventSubscriberInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   the language manager.
   */
  public function __construct(LanguageManagerInterface $language_manager) {
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST] = ['onRequestEPartner404'];
    return $events;
  }

  /**
   * This method is called whenever the kernel.request event is
   * dispatched.
   *
   * @param RequestEvent $event
   */
  public function onRequestEPartner404(RequestEvent $event): void {
    $request = $event->getRequest();

    // If we've got an exception, nothing to do here.
    if ($request->get('exception') !== NULL) {
      return;
    }

    // This is necessary because this also gets called on
    // node sub-tabs such as "edit", "revisions", etc.  This
    // prevents those pages from redirected.
    if ($request->attributes->get('_route') !== 'entity.node.canonical') {
      return;
    }

    // Only redirect a certain content type.
    if ($request->attributes->get('node')->getType() !== 'epartner') {
      return;
    }
    /** @var \Drupal\node\Entity\Node $node */
    $node = $request->attributes->get('node');
    if (!$node) {
      return;
    }

    $default_language = $node->language() ?: $this->languageManager->getDefaultLanguage();
    $active_language = $this->languageManager->getCurrentLanguage();

    if ($active_language->getId() !== $default_language->getId()) {
      throw new NotFoundHttpException();
    }
  }

}
