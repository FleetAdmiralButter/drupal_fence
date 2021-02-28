<?php

namespace Drupal\drupal_fence;

use Drupal\Core\Cache\CacheBackendInterface;

class DrupalFenceRequestChecker {

    private $threshold;
    private $expiration;
    public function __construct() {
        $this->threshold = \Drupal::config('drupal_fence.settings')->get('drupal_fence.threshold');
        $this->expiration = \Drupal::config('drupal_fence.settings')->get('drupal_fence.expiration');
    }

    public function is_blocked_client($client_identifier) {
        return !(\Drupal::flood()->isAllowed('drupal_fence.security_violation', $this->threshold, $this->expiration, $client_identifier));
    }

    public function log_violation($client_identifier) {
        \Drupal::flood()->register('drupal_fence.security_violation', $this->expiration, $client_identifier);
    }

    private function get_path_cid($path) {
        return 'drupal_fence:checked_path:' . hash('sha1', $path);
    }

    public function check_path($path) {
        $flagged = FALSE;
        if($cache = \Drupal::cache('data')->get($this->get_path_cid($path))) {
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
                ->set($this->get_path_cid($path), 
                      $cached_data, 
                      CacheBackendInterface::CACHE_PERMANENT, 
                      ['drupal_fence_checked_paths']
                    );
        }
        return $flagged;
    }
}