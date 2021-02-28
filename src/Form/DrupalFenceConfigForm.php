<?php

namespace Drupal\drupal_fence\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class DrupalFenceConfigForm extends ConfigFormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'drupal_fence_config_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form = parent::buildForm($form, $form_state);
        $config = $this->config('drupal_fence.settings');

        $form['enabled'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Enable Drupal Fence'),
            '#default_value' => $config->get('drupal_fence.enabled'),
            '#description' => $this->t('Whether Drupal Fence should run. Disabling Drupal Fence when its not needed can improve the performance of uncached requests.'),
        ];
        $form['threshold'] = [
            '#type' => 'number',
            '#title' => $this->t('Threshold'),
            '#default_value' => $config->get('drupal_fence.threshold'),
            '#description' => $this->t('Number of violations a client can trigger before being blocked by Drupal Fence.'),
        ];

        $form['expiration'] = [
            '#type' => 'number',
            '#title' => $this->t('Expiration'),
            '#default_value' => $config->get('drupal_fence.expiration'),
            '#description' => $this->t('How long to track violating clients.'),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {

    }

    /**
     * {@inheritdoc}
     */
    public function getEditableConfigNames() {
        return [
            'drupal_fence.settings'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = $this->config('drupal_fence.settings');
        $config->set('drupal_fence.enabled', $form_state->getValue('enabled'));
        $config->set('drupal_fence.threshold', $form_state->getValue('threshold'));
        $config->set('drupal_fence.expiration', $form_state->getValue('expiration'));
        $config->save();
        return parent::submitForm($form, $form_state);
    }
}