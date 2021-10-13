<?php

namespace Drupal\dbo_pdf\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity_print.view')) {
      $route->setDefaults([
        '_controller' => '\Drupal\dbo_pdf\Controller\DboEntityPrintController::viewPrint',
      ]);
    }
  }

}
