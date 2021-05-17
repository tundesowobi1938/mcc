<?php

namespace Drupal\mccserver\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MccConfigForm.
 */
class MccConfigForm extends ConfigFormBase {

  
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'mccserver.mccconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mcc_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('mccserver.mccconfig');
    $form['target_site_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Target Site Name'),
      '#description' => $this->t('The name of the site to make changes to'),
      '#maxlength' => 255,
      '#size' => 60,
      '#default_value' => $config->get('target_site_name'),
    ];
    $form['target_site_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Target Site URL'),
      '#description' => $this->t('The URL for the target site'),
      '#default_value' => $config->get('target_site_url'),
    ];
    $form['service_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Service Key'),
      '#description' => $this->t('The Key to access the Service'),
      '#maxlength' => 255,
      '#size' => 60,
      '#default_value' => $config->get('service_key'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    
    $this->config('mccserver.mccconfig')
      ->set('target_site_name', $form_state->getValue('target_site_name'))
      ->set('target_site_url', $form_state->getValue('target_site_url'))
      ->set('service_key', $form_state->getValue('service_key'))
      ->save();
  }

}
