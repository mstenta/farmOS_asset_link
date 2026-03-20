<?php

declare(strict_types=1);

namespace Drupal\farmos_asset_link\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Controller\ControllerBase;

/**
 * Defines FarmAssetLinkMapController class.
 */
class FarmAssetLinkMapController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = [
      'map-prototype' => [
        '#type' => 'farm_map',
        '#attributes' => [
          'id' => 'farm-asset-link-map-prototype',
          'data-map-instantiator' => 'farm-asset-link',
        ],
      ],
      '#attached' => [
        'library' => [
          // 'core/drupal',
          'core/drupalSettings',
          // 'farm_map/farm_map',
        ],
      ],
    ];

    $response = new AjaxResponse();

    $response->addCommand(new InsertCommand('#farm-asset-link-map-prototype-target', $build));

    return $response;
  }

}
