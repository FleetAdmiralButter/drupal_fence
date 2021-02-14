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

    public function DrupalFenceLoad(GetResponseEvent $event) {
        // do stuff
        $path = \Drupal::request()->getRequestUri();
        drupal_set_message('DrupalFence event fired at ' . $path);
    }


    /**
     * {@inheritdoc}
     */
    static function getSubscribedEvents() {
        $events[KernelEvents::REQUEST] = array('DrupalFenceLoad', 20);
        return $events;
    }
}