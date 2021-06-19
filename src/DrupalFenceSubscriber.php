<?php

namespace Drupal\drupal_fence;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Drupal\Core\Site\Settings;
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
        $block_response = \Drupal::config('drupal_fence.settings')->get('drupal_fence.block_response');

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
            // Do not cache these
            \Drupal::service('page_cache_kill_switch')->trigger();
            if (!$silent_mode) {
                if ($block_response === '403') {
                  throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
                } else {
                  throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    static function getSubscribedEvents() {
        $events[KernelEvents::REQUEST] = array('DrupalFenceCheckRequest', Settings::get('drupal_fence.listener_priority', 1000));
        return $events;
    }
}
