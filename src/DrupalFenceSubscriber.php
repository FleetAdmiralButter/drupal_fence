<?php

namespace Drupal\drupal_fence;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a DrupalFenceSubscriber
 */
class DrupalFenceSubscriber implements EventSubscriberInterface {

    public function DrupalFenceCheckPath(GetResponseEvent $event) {
        $path = \Drupal::request()->getRequestUri();
        $isAllowed = _drupal_fence_is_allowed(\Drupal::request()->getClientIp());
        $isExploitPath = $this->_drupal_fence_check_path($path);

        if ($isExploitPath) {
            $this->_drupal_fence_log_violation();
        }
        
        if (!$isAllowed || $isExploitPath) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
        }
    }


    /**
     * {@inheritdoc}
     */
    static function getSubscribedEvents() {
        $events[KernelEvents::REQUEST] = array('DrupalFenceCheckPath', 20);
        return $events;
    }

    private function _drupal_fence_is_allowed($client_identifier) {
        return \Drupal::flood()->isAllowed('drupal_fence.security_violation', 5, 3600, $client_identifier);
    }

    private function _drupal_fence_log_violation() {
        $client_identifier = \Drupal::request()->getClientIp();
        \Drupal::flood()->register('drupal_fence.security_violation', 3600, $client_identifier);
    }

    private function _drupal_fence_check_path($path) {
        $database = \Drupal::service('database');
        $result = $database->query("SELECT * FROM {drupal_fence_flagged_routes} WHERE exploit_uri LIKE :current_path",
        [
            ':current_path' => $path,
        ])->fetchAll();

        return (count($result) > 0) ? TRUE : FALSE;
    }
}