<?php

declare(strict_types=1);

namespace Drupal\farmos_asset_link\Hook;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Page hook implementations for farmos_asset_link.
 */
class PageHooks {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public function pageAttachments(array &$attachments) {
    // @todo Add special handling for not-yet-logged-in users.

    $storage = $this->entityTypeManager->getStorage('asset_link_default_plugin');

    $ids = $storage->getQuery()->accessCheck(TRUE)
      ->condition('sidebarUrlPattern', '', '<>')->execute();

    /** @var \Drupal\farmos_asset_link\Entity\AssetLinkDefaultPlugin[] $defaultPluginConfigs */
    $defaultPluginConfigs = $storage->loadMultiple($ids);

    $sidebarUrlPatterns = [];

    foreach ($defaultPluginConfigs as $defaultPluginConfig) {
      if (!$defaultPluginConfig->status()) {
        continue;
      }

      $sidebarUrlPatterns[] = $defaultPluginConfig->sidebarUrlPattern();
    }

    $attachments['#attached']['library'][] = 'farmos_asset_link/farmos_asset_link_sidecar';

    $attachments['#attached']['drupalSettings']['farmos_asset_link'] = [
      'sidebar_url_patterns' => $sidebarUrlPatterns,
    ];

  }

}
