<?php

namespace Drupal\drupal_fence\Controller;

class DrupalFenceController {
    public function hello() {
        return array (
            '#title' => 'Hello World!',
            '#markup' => 'Content is here'
        );
    }
}