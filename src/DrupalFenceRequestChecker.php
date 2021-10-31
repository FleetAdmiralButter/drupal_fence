<?php

namespace Drupal\drupal_fence;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Database\Connection;

class DrupalFenceRequestChecker {

    private CacheBackendInterface $cache;
    private FloodInterface $flood;
    private Connection $database;
    private $threshold;
    private $expiration;
    public function __construct(CacheBackendInterface $cache, ConfigFactory $config_factory, FloodInterface $flood, Connection $database) {
        $this->cache = $cache;
        $this->database = $database;
        $this->flood = $flood;

        $this->threshold = $config_factory->get('drupal_fence.settings')->get('drupal_fence.threshold');
        $this->expiration = $config_factory->get('drupal_fence.settings')->get('drupal_fence.expiration');
    }

    public function is_blocked_client($client_identifier) {
        return !($this->flood->isAllowed('drupal_fence.security_violation', $this->threshold, $this->expiration, $client_identifier));
    }

    public function log_violation($client_identifier) {
        $this->flood->register('drupal_fence.security_violation', $this->expiration, $client_identifier);
    }

    private function get_path_cid($path) {
        return 'drupal_fence:checked_path:' . hash('sha1', $path);
    }

    public function check_path($path) {
        $flagged = FALSE;
        if($cache = $this->cache->get($this->get_path_cid($path))) {
            $flagged = $cache->data['flagged'];
        } else {
            $result = $this->database->query("SELECT exploit_uri FROM {drupal_fence_flagged_routes} WHERE INSTR(:path, exploit_uri) > 0", [
                ':path' => $path
            ])->fetchAll();
            $flagged = count($result) > 0;
            $cached_data = [
                'path' => $path,
                'flagged' => $flagged,
            ];
            $this->cache
                ->set($this->get_path_cid($path),
                      $cached_data,
                      CacheBackendInterface::CACHE_PERMANENT,
                      ['drupal_fence_checked_paths']
                    );
        }
        return $flagged;
    }
}
