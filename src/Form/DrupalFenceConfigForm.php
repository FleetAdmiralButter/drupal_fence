<?php

namespace Drupal\drupal_fence\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;
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
        $listener_priority_text = Settings::get('drupal_fence.listener_priority') ? 'Listener priority is ' . Settings::get('drupal_fence.listener_priority') : 'drupal_fence.listener_priority is not configured in settings.php. Defaulting to a priority of 1000.';

        $form['enabled'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Enable Drupal Fence'),
            '#default_value' => $config->get('enabled'),
            '#description' => $this->t('Whether Drupal Fence should run. Disabling Drupal Fence when its not needed can improve the performance of uncached requests.'),
        ];
        $form['silent_mode'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Silent Mode'),
            '#default_value' => $config->get('silent_mode'),
            '#description' => $this->t('If enabled, Drupal Fence will log clients that try to access flagged routes, but will not do any blocking. Please note that logging will still stop at the configured threshold level to prevent a database overload in the event of an DoS attack.'),
        ];
        $form['threshold'] = [
            '#type' => 'number',
            '#title' => $this->t('Threshold'),
            '#default_value' => $config->get('threshold'),
            '#description' => $this->t('Number of violations a client can trigger before being blocked by Drupal Fence.'),
        ];

        $form['expiration'] = [
            '#type' => 'number',
            '#title' => $this->t('Expiration'),
            '#default_value' => $config->get('expiration'),
            '#description' => $this->t('How long to track violating clients.'),
        ];

        $form['block_response'] = [
          '#type' => 'select',
          '#title' => $this
            ->t('Block Response'),
          '#description' => $this->t('The HTTP status returned when blocking a client.'),
          '#default_value' => $config->get('block_response'),
          '#options' => [
            '403' => $this->t('403 Access Denied'),
            '404' => $this->t('404 Not Found'),
          ],
        ];

        $form['priority'] = array(
          '#type' => 'fieldset',
          '#title' => $this->t('KernelEvent Listener Priority'),
        );

        $form['priority']['listener_priority'] = [
          '#type' => 'label',
          '#title' => $this->t($listener_priority_text),
        ];

        $form['clear'] = array(
          '#type' => 'fieldset',
          '#title' => $this->t('Clear data'),
        );

        $form['clear']['clear_log'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Clear tracking data on save?'),
            '#default_value' => 0,
            '#description' => $this->t('This will delete any events logged in the flood control system, and unblock any currently blocked clients (if any).'),
        ];

        return $form;
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
        $config->set('enabled', $form_state->getValue('enabled'));
        $config->set('silent_mode', $form_state->getValue('silent_mode'));
        $config->set('threshold', $form_state->getValue('threshold'));
        $config->set('expiration', $form_state->getValue('expiration'));
        $config->set('block_response', $form_state->getValue('block_response'));
        $config->save();

        if ($form_state->getValue('clear_log')) {
            \Drupal::service('database')->delete('flood')
            ->condition('event', 'drupal_fence.security_violation')
            ->execute();
        }
        return parent::submitForm($form, $form_state);
    }
}
