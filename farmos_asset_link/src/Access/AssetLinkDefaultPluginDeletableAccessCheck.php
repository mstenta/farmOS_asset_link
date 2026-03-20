<?php

declare(strict_types=1);

namespace Drupal\farmos_asset_link\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Deletable plugin access logic.
 *
 * Checks whether delete routes are accessible based on whether default plugins
 * are user defined.
 */
class AssetLinkDefaultPluginDeletableAccessCheck implements AccessInterface {

  /**
   * Filter delete routes for non-user-defined default plugins.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function access(RouteMatchInterface $route_match) {

    // If there is no "asset_link_default_plugin" parameter, bail.
    $plugin = $route_match->getParameter('asset_link_default_plugin');

    if ($plugin && $plugin->userDefined()) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
