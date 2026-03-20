<?php

declare(strict_types=1);

namespace Drupal\farmos_asset_link\Form;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for the Asset Link default plugin add and edit forms.
 */
class AssetLinkDefaultPluginForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $plugin = $this->entity;

    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#maxlength' => 255,
      '#default_value' => $plugin->url(),
      '#disabled' => !$this->entity->isNew(),
      '#description' => $this->t("URL of the plugin."),
      '#required' => TRUE,
    ];

    $form['sidebarUrlPattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sidebar URL Pattern'),
      '#maxlength' => 255,
      '#default_value' => $plugin->sidebarUrlPattern(),
      '#description' => $this->t("URL whitelist regex for which pages the sidebar loads on"),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $this->entity->status(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    $plugin = $this->entity;
    if (!$plugin->userDefined()) {
      unset($actions['delete']);
    }

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $plugin = $this->entity;

    if (empty($plugin->id())) {
      $plugin->setId(Crypt::hashBase64($plugin->url()));
      $plugin->setUserDefined(TRUE);
    }

    $status = $plugin->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('Default plugin added: %url.', [
        '%url' => $plugin->url(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('Default plugin updated: %url', [
        '%url' => $plugin->url(),
      ]));
    }

    $form_state->setRedirect('entity.asset_link_default_plugin.collection');
  }

  /**
   * Helper function to check whether an Example configuration entity exists.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('asset_link_default_plugin')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
