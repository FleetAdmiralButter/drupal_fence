<?php

namespace Drupal\drupal_fence\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;
use Symfony\Component\Yaml\Yaml;

use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DrupalFenceImportForm extends ConfigFormBase {

    protected $messenger;
    public function __construct(ConfigFactoryInterface $config_factory, MessengerInterface $messenger)
    {
        parent::__construct($config_factory);
        $this->messenger = $messenger;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('config.factory'),
            $container->get('messenger')
        );
    }

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
        $config = $this->config('drupal_fence.settings');
        $form = parent::buildForm($form, $form_state);

        $form['data'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Import Data'),
            '#default_value' => implode("\r\n", $config->get('urls')),
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
    public function getEditableConfigNames() {
        return [
            'drupal_fence.settings'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        parent::submitForm($form, $form_state);
        $config = $this->config('drupal_fence.settings');
        $config->set('urls', explode("\r\n", $form_state->getValue('data')));
        $config->save();

        Cache::invalidateTags(['drupal_fence_checked_paths']);
        $this->messenger->addMessage('The URLs were imported successfully.');
    }
}
