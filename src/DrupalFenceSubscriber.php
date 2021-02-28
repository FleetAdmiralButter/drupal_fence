<?php

namespace Drupal\drupal_fence;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Provides a DrupalFenceSubscriber
 */
class DrupalFenceSubscriber implements EventSubscriberInterface {

    public function DrupalFenceCheckRequest(GetResponseEvent $event) {
        $client_identifier = \Drupal::request()->getClientIp();
        $path = \Drupal::request()->getRequestUri();

        $isBlocked = \Drupal::service('drupal_fence.request_checker')->is_blocked_client($client_identifier);
        $isExploitPath = \Drupal::service('drupal_fence.request_checker')->check_path($path);

        // Only log if the client is not yet blocked by flood control to avoid overloading the database.
        if (!$isBlocked && $isExploitPath) {
            \Drupal::service('drupal_fence.request_checker')->log_violation($client_identifier);
        }
        
        if ($isBlocked || $isExploitPath) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
        }
    }

    /**
     * {@inheritdoc}
     */
    static function getSubscribedEvents() {
        // A priority of 150 was chosen so that Drupal Fence fires before Fast404, but after Static Page Cache. 
        $events[KernelEvents::REQUEST] = array('DrupalFenceCheckRequest', 150);
        return $events;
    }
}