<?php

namespace Drupal\agreement\Routing;

use Symfony\Component\Routing\Route;

/**
 * Defines a route subscriber to register a url for serving image styles.
 */
class AgreementRoutes {

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes() {

    $config = \Drupal::config('agreement.settings');
    $agreement_url = $config->get('page_url');
    $agreement_page_title = $config->get('page_title');

    $user = \Drupal::currentUser();

    $routes = [];

    $routes['agreement.page'] = new Route(
      '/' . $agreement_url,
      [
        '_form' => 'Drupal\agreement\Form\AgreementPage',
        '_title' => $agreement_page_title,
      ],
      [
        '_permission' => 'access content',
      ]
    );

    return $routes;
  }

}
