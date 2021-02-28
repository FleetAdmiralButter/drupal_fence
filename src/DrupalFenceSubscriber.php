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
        
        if (\Drupal::config('drupal_fence.settings')->get('drupal_fence.enabled') == 0) {
            return;
        }
        
        $client_identifier = \Drupal::request()->getClientIp();
        $path = \Drupal::request()->getRequestUri();

        $isBlocked = \Drupal::service('drupal_fence.request_checker')->is_blocked_client($client_identifier);
        $isExploitPath = \Drupal::service('drupal_fence.request_checker')->check_path($path);

        if ($isExploitPath) {
            // Only log if the client is not yet blocked by flood control to avoid overloading the database.
            if (!$isBlocked) {
                \Drupal::service('drupal_fence.request_checker')->log_violation($client_identifier);
            }
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