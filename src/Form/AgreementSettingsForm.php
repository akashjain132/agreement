<?php

namespace Drupal\agreement\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
 * Class AgreementSettingsForm.
 *
 * @package Drupal\agreement\Form
 */
class AgreementSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'agreement.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'agreement_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('agreement.settings');

    $form['text'] = [
      '#title' => $this->t('Agreement Text'),
      '#type' => 'text_format',
      '#description' => $this->t('This is the agreement text.'),
      '#default_value' => $config->get('text'),
      '#required' => TRUE,
      '#format' => 'basic_html',
    ];

    $frequency = array($this->t('Only once'), $this->t('On every log on'));

    $form['frequency'] = [
      '#title' => $this->t('Frequency'),
      '#type' => 'select',
      '#description' => $this->t('How often should users be required to accept the agreement?'),
      '#default_value' => $config->get('frequency'),
      '#required' => TRUE,
      '#options' => $frequency,
    ];

    $form['page_url'] = [
      '#title' => $this->t('Agreement Page URL'),
      '#type' => 'textfield',
      '#description' => $this->t('At what URL should the agreement page be located?'),
      '#default_value' => $config->get('page_url'),
      '#required' => TRUE,
    ];

    $form['page_title'] = [
      '#title' => $this->t('Agreement Page Title'),
      '#type' => 'textfield',
      '#description' => $this->t('What should the title of the agreement page be?'),
      '#default_value' => $config->get('page_title'),
      '#required' => TRUE,
    ];

    $form['checkbox_text'] = [
      '#title' => $this->t('Agreement Checkbox Text'),
      '#type' => 'textfield',
      '#description' => $this->t('This text will be displayed next to the "I agree" checkbox.'),
      '#default_value' => $config->get('checkbox_text'),
      '#required' => TRUE,
    ];

    $form['submit_text'] = [
      '#title' => $this->t('Agreement Submit Text'),
      '#type' => 'textfield',
      '#description' => t('This text will be displayed on the "Submit" button.'),
      '#default_value' => $config->get('submit_text'),
      '#required' => TRUE,
    ];

    $form['success_message'] = [
      '#title' => $this->t('Agreement Success Message'),
      '#type' => 'textfield',
      '#description' => $this->t('This text will be displayed on the "Submit" button.'),
      '#default_value' => $config->get('success_message'),
      '#required' => FALSE,
    ];

    $form['failure_message'] = [
      '#title' => $this->t('Agreement Failure Message'),
      '#type' => 'textfield',
      '#description' => $this->t('What message should be displayed to the users if they do not accept the agreement?'),
      '#default_value' => $config->get('failure_message'),
      '#required' => FALSE,
    ];

    $form['success_destination'] = [
      '#title' => $this->t('Agreement Success Destination'),
      '#type' => 'textfield',
      '#description' => $this->t('What page should be displayed after the user accepts the agreement? Leave blank to go to original destination that triggered the agreement. <front> is the front page. Users who log in via the one-time login link will always be redirected to their user profile to change their password.'),
      '#default_value' => $config->get('success_destination'),
      '#required' => FALSE,
    ];

    $visibility = $this->buildVisibilityInterface([], $form_state);
    $form = array_merge($form, $visibility);

    return parent::buildForm($form, $form_state);
  }

  /**
   * Helper function for building the visibility UI form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form array with the visibility UI added in.
   */
  protected function buildVisibilityInterface(array $form, FormStateInterface $form_state) {
    $config = $this->config('agreement.settings');

    $form['visibility_tabs'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Visibility'),
      '#parents' => ['visibility_tabs'],
    ];

    $form['request_path'] = array(
      '#type' => 'details',
      '#title' => t('Pages'),
      '#title_display' => 'invisible',
      '#group' => 'visibility_tabs',
    );

    $form['request_path']['pages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Pages'),
      '#description' => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. An example path is %user-wildcard for every user page. %front is the front page.", [
        '%user-wildcard' => '/user/*',
        '%front' => '<front>',
      ]),
      '#default_value' => $config->get('pages'),
    ];

    $form['request_path']['page_negate'] = [
      '#type' => 'radios',
      '#title' => $this->t('Page Negate'),
      '#title_display' => 'invisible',
      '#options' => [
        $this->t('Show on every page except the listed pages.'),
        $this->t('Show on only the listed pages.'),
      ],
      '#default_value' => $config->get('page_negate'),
    ];

    $form['user_role'] = array(
      '#type' => 'details',
      '#title' => t('User Roles'),
      '#title_display' => 'invisible',
      '#group' => 'visibility_tabs',
    );

    $roles = array_map('\Drupal\Component\Utility\Html::escape', user_role_names());
    unset($roles['anonymous']);

    $form['user_role']['roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('When the user has the following roles'),
      '#options' => $roles,
      '#default_value' => $config->get('roles'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $user_input = $form_state->getValues();

    $this->config('agreement.settings')
      ->set('text', $user_input['text']['value'])
      ->set('frequency', $user_input['frequency'])
      ->set('page_url', $user_input['page_url'])
      ->set('page_title', $user_input['page_title'])
      ->set('checkbox_text', $user_input['checkbox_text'])
      ->set('submit_text', $user_input['submit_text'])
      ->set('success_message', $user_input['success_message'])
      ->set('failure_message', $user_input['failure_message'])
      ->set('pages', $user_input['pages'])
      ->set('page_negate', $user_input['page_negate'])
      ->set('roles', $user_input['roles'])
      ->save();

    // Rebuild the menu router.
    \Drupal::service('router.builder')->rebuild();
  }

}
