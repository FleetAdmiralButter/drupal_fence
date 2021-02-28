<?php

namespace Drupal\drupal_fence;

use Drupal\Core\Archiver\Zip;
use Drupal\Core\StreamWrapper\PublicStream;

class DrupalFenceVerifySiteBatchProcessor {

    public function drupalFenceBatchProcessorCallback($stage, &$context) {
        return;
        switch($stage) {
            case 'flush_drupal_caches':
                $context['message'] = 'Flushing Drupal caches...';
                drupal_flush_all_caches();
                break;
            case 'download_fresh_drupal':
                $context['message'] = 'Downloading a fresh copy of Drupal...';
                self::downloadFreshDrupal();
                break;
            case 'unpack_drupal':
                $context['message'] = 'Unpacking...';
                self::unpackDrupal();
                break;
            case 'cleanup':
                $context['message'] = 'Cleaning up...';
                self::clean();
                break;
        }
    }

    public function downloadFreshDrupal() {
        $drupal_version = \Drupal::VERSION;
        $dir = 'public://';
        $drupal_download_link = 'https://ftp.drupal.org/files/projects/drupal-' . $drupal_version . '.zip';
        $drupal = file_get_contents($drupal_download_link);
        \Drupal::service('file_system')->saveData($drupal, $dir . '/drupal_current.zip', EXISTS_REPLACE);
    }

    public function unpackDrupal() {
        $zip = new Zip(PublicStream::basePath() . '/drupal_current.zip');
        $zip->extract(PublicStream::basePath() . '/drupal_fence_data');
    }

    public function clean() {
        \Drupal::service('file_system')->delete(PublicStream::basePath() . '/drupal_current.zip');
        \Drupal::service('file_system')->deleteRecursive(PublicStream::basePath() . '/drupal_fence_data');
    }
}