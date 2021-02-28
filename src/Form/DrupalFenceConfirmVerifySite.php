<?php

namespace Drupal\drupal_fence\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Defines a confirmation form to confirm deletion of something by id.
 */
class DrupalFenceConfirmVerifySite extends FormBase {
  
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, string $id = NULL) {

      $form['warning'] = [
        '#type' => 'label',
        '#title' => $this->t('WARNING! This process will flush the Drupal cache. This may cause performance issues if your site is currently under heavy load.'),
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
    public function submitForm(array &$form, FormStateInterface $form_state) {
      $batch = array(
        'title' => $this->t('Processing'),
        'operations' => [
          [
            '\Drupal\drupal_fence\DrupalFenceVerifySiteBatchProcessor::drupalFenceBatchProcessorCallback',   
            ['flush_drupal_caches'],
          ],
          [
            '\Drupal\drupal_fence\DrupalFenceVerifySiteBatchProcessor::drupalFenceBatchProcessorCallback', 
            ['download_fresh_drupal'],
          ],
          [
            '\Drupal\drupal_fence\DrupalFenceVerifySiteBatchProcessor::drupalFenceBatchProcessorCallback', 
            ['unpack_drupal'],
          ],
          [
            '\Drupal\drupal_fence\DrupalFenceVerifySiteBatchProcessor::drupalFenceBatchProcessorCallback', 
            ['cleanup'],
          ],
        ],
        'init_message' => t('Initializing...'),
        'progress_message' => t('Stage @current of @total. Estimated time: @estimate.'),
        'error_message' => t('An error occurred when trying to verify your site.')
      );

      batch_set($batch);
    }
  
    /**
     * {@inheritdoc}
     */
    public function getFormId() : string {
      return "confirm_verify_site_form";
    }
  
  }