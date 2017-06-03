<?php

namespace Drupal\agreement\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;

class AgreementSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {

    $events[KernelEvents::REQUEST][] = ['checkAgreement', 100];
    return $events;
  }

  /**
   * Redirects anonymous users to the /user route
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   */
  public function checkAgreement(GetResponseEvent $event) {

    $config = \Drupal::config('agreement.settings');

    $currentUser = \Drupal::currentUser();
    $currentPath = $event->getRequest()->getPathInfo();

    if ($currentUser->isAnonymous()) {
      return;
    }

    if ($currentPath == Url::fromRoute('user.logout')->toString()) {
      return;
    }

    $pages = $config->get('pages');
    $page_negate = $config->get('page_negate');
    if (($this->evaluate($pages) && $page_negate == '0') || (!$this->evaluate($pages) && $page_negate == '1')) {
      return;
    }

    $frequency = $config->get('frequency');

    if ($frequency == '0') {
      $userData = \Drupal::service('user.data');
      $isAgreementApproved = $userData->get('agreement', $currentUser->id(), 'agreement');

      if (!empty($isAgreementApproved)) {
        return;
      }
    }
    elseif ($frequency == '1') {
      $isAgreementApproved = isset($_SESSION['agreement']) ? $_SESSION['agreement'] : FALSE;

      if (!empty($isAgreementApproved)) {
        return;
      }
    }

    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();

    $applied_roles = $config->get('roles');
    $applied_roles = array_filter($applied_roles);

    if (!array_intersect($applied_roles, $roles)) {
      return;
    }

    if ($currentPath !== Url::fromRoute('agreement.page')->toString()) {
      $response = new RedirectResponse(Url::fromRoute('agreement.page')->toString());
      $response->send();
      exit;
    }

  }

  public function evaluate($pages) {

    // Convert path to lowercase. This allows comparison of the same path
    // with different case. Ex: /Page, /page, /PAGE.
    $pages = Unicode::strtolower($pages);
    if (!$pages) {
      return TRUE;
    }
    $pages = str_replace('<front>', '/', $pages);

    $path = \Drupal::service('path.current')->getPath();

    // Compare the lowercase path alias (if any) and internal path.
    // Do not trim a trailing slash if that is the complete path.
    $path = $path === '/' ? $path : rtrim($path, '/');
    $path_alias = Unicode::strtolower(\Drupal::service('path.alias_manager')->getAliasByPath($path));

    $page_match = \Drupal::service('path.matcher')->matchPath($path_alias, $pages) || (($path != $path_alias) && \Drupal::service('path.matcher')->matchPath($path, $pages));

    return $page_match;
  }

}
