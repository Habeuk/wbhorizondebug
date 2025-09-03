<?php
declare(strict_types = 1);

namespace Drupal\wbhorizondebug\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for wbhorizondebug routes.
 */
final class GenerateEntitiesController extends ControllerBase {
  
  /**
   * Builds the response.
   */
  public function __invoke(): array {
    $build['content'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => 'Chargement ...',
      '#attributes' => [
        'id' => 'app-enerate-entities'
      ],
      '#attached' => [
        'library' => [
          'wbhorizondebug/buildinterface'
        ]
      ]
    ];
    return $build;
  }
}
