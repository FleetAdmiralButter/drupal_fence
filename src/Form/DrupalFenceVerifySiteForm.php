<?php

namespace Drupal\drupal_fence\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Symfony\Component\Yaml\Yaml;

class DrupalFenceVerifySiteForm extends ConfigFormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'drupal_fence_verify_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form = parent::buildForm($form, $form_state);
        
        $form['about'] = [
            '#type' => 'label',
            '#title' => $this->t('Drupal Fence will now start verifying the integrity of core Drupal files.'),
        ];

        $form['about2'] = [
            '#type' => 'label',
            '#title' => $this->t('Please note, Drupal Fence will not attempt repairs. It is up to the site administrator to replace damaged files.'),
        ];
        


        $form['actions']['submit'] = array(
            '#type' => 'submit',
            '#value' => $this
              ->t('NOT YET IMPLEMENTED - THIS DOES NOTHING USEFUL'),
          );
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
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $form_state->setRedirectUrl(Url::fromRoute('drupal_fence.verify_confirm'));
    }
}