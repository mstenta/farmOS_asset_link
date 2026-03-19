<?php

declare(strict_types=1);

namespace Drupal\farmos_asset_link\Hook;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteObjectInterface;
use Drupal\jsonapi\ResourceType\ResourceType;

/**
 * Entity hook implementations for farmos_asset_link.
 */
class EntityHooks {

  public function __construct(
    protected RouteMatchInterface $routeMatch,
  ) {}

  /**
   * Implements hook_entity_create_access().
   *
   * Grant JSON:API file upload permissions for entity fields based on
   * bundle-level permissions. e.g. Allow image uploads for animal assets if the
   * user has 'create animal asset' permissions.
   *
   * @todo Follow up on farmOS core issue: Fix JSON:API permission issue so non-admin users can upload files #563
   * @see https://github.com/farmOS/farmOS/pull/563#issuecomment-1241952618
   */
  #[Hook('entity_create_access')]
  public function entityCreateAccess($account, $context, $entity_bundle) {

    $resource_type = $this->routeMatch->getParameter("resource_type");

    if (!($resource_type instanceof ResourceType)) {
      return AccessResult::neutral();
    }

    $entity_type_id = $resource_type->getEntityTypeId();
    $entity_bundle = $resource_type->getBundle();

    if (empty($entity_type_id) || empty($entity_bundle)) {
      return AccessResult::neutral();
    }

    $route = $this->routeMatch->getRouteObject();

    if (empty($route) || $route->getDefault(RouteObjectInterface::CONTROLLER_NAME) != 'jsonapi.file_upload:handleFileUploadForNewResource') {
      return AccessResult::neutral();
    }

    return AccessResult::allowedIfHasPermission($account, "create " . $entity_bundle . " " . $entity_type_id);
  }

}
