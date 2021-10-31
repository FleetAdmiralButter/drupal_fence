<?php

namespace Drupal\drupal_fence;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use Drupal\Core\Site\Settings;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;

/**
 * Provides a DrupalFenceSubscriber
 */
class DrupalFenceSubscriber implements EventSubscriberInterface {

    private $config_factory;
    private $request;
    private $drupal_fence_request_checker;
    private $page_cache_kill_switch;
    public function __construct(ConfigFactory $config_factory, RequestStack $request, DrupalFenceRequestChecker $drupal_fence_request_checker, KillSwitch $page_cache_kill_switch) {
        $this->config_factory = $config_factory;
        $this->request = $request->getCurrentRequest();
        $this->drupal_fence_request_checker = $drupal_fence_request_checker;
        $this->page_cache_kill_switch = $page_cache_kill_switch;
    }

    public function onKernelRequest($event) {

        // Don't run if Drupal Fence is disabled or this isn't a master request
        if (($this->config_factory->get('drupal_fence.settings')->get('drupal_fence.enabled') == 0) || !($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST)) {
            return;
        }

        $silent_mode = $this->config_factory->get('drupal_fence.settings')->get('drupal_fence.silent_mode');
        $block_response = $this->config_factory->get('drupal_fence.settings')->get('drupal_fence.block_response');

        $client_identifier = $this->request->getClientIp();
        $path = $this->request->getRequestUri();

        $is_blocked = $this->drupal_fence_request_checker->is_blocked_client($client_identifier);
        $is_exploit_path = $this->drupal_fence_request_checker->check_path($path);

        // Only log if the client is not already blocked by flood control.
        if ($is_exploit_path) {
            if (!$is_blocked) {
                $this->drupal_fence_request_checker->log_violation($client_identifier);
            }
        }

        // Block client if path matches or if they have been blocked by flood control and silent mode is disabled.
        if ($is_exploit_path || $is_blocked) {
            // Do not cache these
            $this->page_cache_kill_switch->trigger();
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
        $events[KernelEvents::REQUEST] = array('onKernelRequest', Settings::get('drupal_fence.listener_priority', 1000));
        return $events;
    }
}
