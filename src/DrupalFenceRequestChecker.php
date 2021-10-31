<?php

namespace Drupal\drupal_fence;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Flood\FloodInterface;

class DrupalFenceRequestChecker {

    private CacheBackendInterface $cache;
    private FloodInterface $flood;
    private $threshold;
    private $expiration;
    private $urls;
    public function __construct(CacheBackendInterface $cache, ConfigFactory $config_factory, FloodInterface $flood) {
        $this->cache = $cache;
        $this->flood = $flood;

        $this->urls = $config_factory->get('drupal_fence.settings')->get('urls');
        $this->threshold = $config_factory->get('drupal_fence.settings')->get('threshold');
        $this->expiration = $config_factory->get('drupal_fence.settings')->get('expiration');
    }

    public function is_blocked_client($client_identifier) {
        return !($this->flood->isAllowed('drupal_fence.security_violation', $this->threshold, $this->expiration, $client_identifier));
    }

    public function log_violation($client_identifier) {
        $this->flood->register('drupal_fence.security_violation', $this->expiration, $client_identifier);
    }

    private function get_path_cid($path) {
        return 'drupal_fence:checked_path:' . hash('sha256', $path);
    }

    public function check_path($path) {
        $flagged = FALSE;
        if($cache = $this->cache->get($this->get_path_cid($path))) {
            $flagged = $cache->data['flagged'];
        } else {
            foreach ($this->urls as $url) {
                if (strpos($path, $url) !== false) {
                    $flagged = TRUE;
                    break;
                }
            }
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
