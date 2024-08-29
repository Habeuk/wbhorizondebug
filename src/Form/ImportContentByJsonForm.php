<?php

namespace Drupal\wbhorizondebug\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a wbhorizondebug form.
 */
class ImportContentByJsonForm extends FormBase {
  
  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wbhorizondebug_import_content_by_json';
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['entity_type'] = [
      '#type' => 'select',
      '#options' => [
        '' => ''
      ] + $this->loadentitiesDefinitions(),
      '#title' => "Type d'entite",
      "#default_value" => '',
      "#required" => TRUE
    ];
    
    $form['entity_json'] = [
      '#type' => 'textarea',
      '#title' => 'Entity json',
      '#required' => TRUE
    ];
    
    $form['actions'] = [
      '#type' => 'actions'
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send')
    ];
    
    return $form;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // if (mb_strlen($form_state->getValue('message')) < 10) {
    // $form_state->setErrorByName('message', $this->t('Message should be at
    // least 10 characters.'));
    // }
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $entityJson = $form_state->getValue('entity_json');
    $entiTyArray = Json::decode($entityJson);
    // dd($entiTyArray);
    $storage = \Drupal::entityTypeManager()->getStorage($form_state->getValue('entity_type'));
    if ($storage) {
      $NewEntity = $storage->create($entiTyArray);
      $oldEntity = $storage->load($NewEntity->id());
      if ($NewEntity->isNew() && !$oldEntity) {
        $NewEntity->save();
        $this->messenger()->addStatus("L'entite a été creer. Id : " . $NewEntity->id());
      }
      else {
        $this->messenger()->addWarning("L'entite existe deja");
      }
    }
  }
  
  protected function loadentitiesDefinitions() {
    $definitions = \Drupal::entityTypeManager()->getDefinitions();
    $entitiesOptions = [];
    foreach ($definitions as $definition) {
      $entitiesOptions[$definition->id()] = $definition->id() . ' _ ' . $definition->getLabel();
    }
    asort($entitiesOptions);
    return $entitiesOptions;
  }
  
}
