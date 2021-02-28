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
        $path = \Drupal::request()->getRequestUri();
        $isAllowed = $this->_drupal_fence_is_allowed(\Drupal::request()->getClientIp());
        $isExploitPath = $this->_drupal_fence_check_path($path);

        // Only log if the request is allowed to prevent an attacker from overloading the database.
        if ($isAllowed && $isExploitPath) {
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
        // A priority of 150 was chosen so that Drupal Fence fires before Fast404, but after Static Page Cache. 
        $events[KernelEvents::REQUEST] = array('DrupalFenceCheckRequest', 150);
        return $events;
    }

    private function _drupal_fence_is_allowed($client_identifier) {
        return \Drupal::flood()->isAllowed('drupal_fence.security_violation', \Drupal::config('drupal_fence.settings')->get('drupal_fence.threshold'), \Drupal::config('drupal_fence.settings')->get('drupal_fence.expiration'), $client_identifier);
    }

    private function _drupal_fence_log_violation() {
        $client_identifier = \Drupal::request()->getClientIp();
        \Drupal::flood()->register('drupal_fence.security_violation', \Drupal::config('drupal_fence.settings')->get('drupal_fence.expiration'), $client_identifier);
    }

    private function _drupal_fence_get_cid($path) {
        return 'drupal_fence:checked_path:' . hash('sha1', $path);
    }

    private function _drupal_fence_check_path($path) {
        $flagged = FALSE;
        if($cache = \Drupal::cache('data')->get($this->_drupal_fence_get_cid($path))) {
            $flagged = $cache->data['flagged'];
        } else {
            $database = \Drupal::service('database');
            $result = $database->query("SELECT exploit_uri FROM {drupal_fence_flagged_routes} WHERE INSTR(:path, exploit_uri) > 0", [
                ':path' => $path
            ])->fetchAll();
            $flagged = (count($result) > 0) ? TRUE : FALSE;
            $cached_data = [
                'path' => $path,
                'flagged' => $flagged,
            ];
            \Drupal::cache('data')
                ->set($this->_drupal_fence_get_cid($path), 
                      $cached_data, 
                      CacheBackendInterface::CACHE_PERMANENT, 
                      ['drupal_fence_checked_paths']
                    );
        }
        return $flagged;
    }
}