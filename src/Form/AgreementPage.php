<?php

namespace Drupal\agreement\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Class AgreementPage.
 *
 * @package Drupal\agreement\Form
 */
class AgreementPage extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'agreement_page';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $currentUser = \Drupal::currentUser();

    $userData = \Drupal::service('user.data');
    $isAgreementApproved = $userData->get('agreement', $currentUser->id(), 'agreement');

    $config = \Drupal::config('agreement.settings');

    $title = $config->get('page_title');
    $agreement_checkbox_title = $config->get('checkbox_text');
    $agreement_text = $config->get('text');
    $submit_text = $config->get('submit_text');
    $frequency = $config->get('frequency');

    if ($frequency == '1') {
      $isAgreementApproved = isset($_SESSION['agreement']) ? $_SESSION['agreement'] : FALSE;
    }

    if (!empty($isAgreementApproved)) {
      $response = new RedirectResponse(Url::fromRoute('<front>')->toString());
      $response->send();
      return;
    }

    $form['agreement'] = array(
      '#type' => 'fieldset',
      '#title' => !empty($title) ? $title : t('Terms of Use'),
    );

    $form['agreement']['agreement_text'] = array(
      '#type' => 'markup',
      '#markup' => $agreement_text,
    );

    $form['agreement']['agreement_approval'] = array(
      '#type' => 'checkbox',
      '#title' => !empty($agreement_checkbox_title) ? $agreement_checkbox_title : $this->t('I agree with these terms'),
      '#default_value' => $approveTermsOfUse,
    );

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $submit_text,
      '#attributes' => array('class' => array('procced-button')),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $config = \Drupal::config('agreement.settings');
    $userData = \Drupal::service('user.data');
    $currentUser = \Drupal::currentUser();
    $failureMessage = $config->get('failure_message');

    $agreement_approval = $form_state->getValue('agreement_approval');

    if (empty($agreement_approval)) {
      $form_state->setErrorByName('agreement_approval', $failureMessage);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = \Drupal::config('agreement.settings');
    $userData = \Drupal::service('user.data');
    $currentUser = \Drupal::currentUser();
    $successMessage = $config->get('success_message');
    $frequency = $config->get('frequency');

    if ($frequency == '1') {
      $_SESSION['agreement'] = TRUE;
    }

    $userData->set('agreement', $currentUser->id(), 'agreement', TRUE);

    drupal_set_message($successMessage, 'status');

    if (!empty($success_destination)) {
      $response = new RedirectResponse(Url::fromRoute($success_destination)->toString());
      $response->send();
      return;
    }
  }

}
