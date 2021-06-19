<?php

namespace Drupal\drupal_fence\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;
use Symfony\Component\Yaml\Yaml;

class DrupalFenceImportForm extends ConfigFormBase {
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

        $form['data'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Import Data'),
            '#default_value' => '',
            '#description' => $this->t('Import URLs.'),
        ];

        $form['warning'] = [
            '#type' => 'label',
            '#title' => $this->t('WARNING! This will replace all currently saved exploit URLs.'),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        $data = $form_state->getValue('data');
        try {
            $data = Yaml::parse($data);
            if (count($data['urls']) < 1) {
                $form_state->setErrorByName('data', $this->t('URL field is required.'));
            }
        } catch (\Exception $e) {
            $form_state->setErrorByName('data', $this->t('Failed to parse YML input.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEditableConfigNames() {
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $urls = Yaml::parse($form_state->getValue('data'));
        $database = \Drupal::database();
        $database->truncate('drupal_fence_flagged_routes')->execute();
        foreach ($urls['urls'] as $url) {
            $database->insert('drupal_fence_flagged_routes')->fields(['exploit_uri' => $url])->execute();
        }
        Cache::invalidateTags(['drupal_fence_checked_paths']);
        \Drupal::service('messenger')->addMessage('The URLs were imported successfully.');
    }
}
