<?php

namespace Drupal\wbhorizondebug\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for wbhorizondebug routes.
 */
class WbhorizondebugController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
