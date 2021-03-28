<?php

namespace Drupal\drupal_fence;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Provides a DrupalFenceSubscriber
 */
class DrupalFenceSubscriber implements EventSubscriberInterface {

    public function DrupalFenceCheckRequest(GetResponseEvent $event) {
        
        // Don't run if Drupal Fence is disabled or this isn't a master request
        if ((\Drupal::config('drupal_fence.settings')->get('drupal_fence.enabled') == 0) || !($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST)) {
            return;
        }

        $silent_mode = \Drupal::config('drupal_fence.settings')->get('drupal_fence.silent_mode');
        
        $client_identifier = \Drupal::request()->getClientIp();
        $path = \Drupal::request()->getRequestUri();

        $is_blocked = \Drupal::service('drupal_fence.request_checker')->is_blocked_client($client_identifier);
        $is_exploit_path = \Drupal::service('drupal_fence.request_checker')->check_path($path);
        
        // Only log if the client is not already blocked by flood control.
        if ($is_exploit_path) {
            if (!$is_blocked) {
                \Drupal::service('drupal_fence.request_checker')->log_violation($client_identifier);
            }
        }

        // Block client if path matches or if they have been blocked by flood control and silent mode is disabled.
        if ($is_exploit_path || $is_blocked) {
            if (!$silent_mode) {
                throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
            }
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