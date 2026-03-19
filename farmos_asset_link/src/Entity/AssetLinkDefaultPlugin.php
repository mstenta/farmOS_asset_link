<?php

declare(strict_types=1);

namespace Drupal\farmos_asset_link\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\entity\EntityPermissionProvider;
use Drupal\entity\Routing\DefaultHtmlRouteProvider;
use Drupal\farmos_asset_link\Controller\AssetLinkDefaultPluginListBuilder;
use Drupal\farmos_asset_link\Form\AssetLinkDefaultPluginForm;

/**
 * Defines the AssetLinkDefaultPlugin entity.
 */
#[ConfigEntityType(
  id: 'asset_link_default_plugin',
  label: new TranslatableMarkup('Asset Link default plugin'),
  label_collection: new TranslatableMarkup('Asset Link default plugins'),
  label_singular: new TranslatableMarkup('Asset Link default plugin'),
  label_plural: new TranslatableMarkup('Asset Link default plugins'),
  config_prefix: 'asset_link_default_plugin',
  entity_keys: [
    'id' => 'id',
    'url' => 'url',
    'sidebarUrlPattern' => 'sidebarUrlPattern',
    'user_defined' => 'user_defined',
    'status' => 'status',
  ],
  handlers: [
    'list_builder' => AssetLinkDefaultPluginListBuilder::class,
    'form' => [
      'add' => AssetLinkDefaultPluginForm::class,
      'edit' => AssetLinkDefaultPluginForm::class,
      'delete' => EntityDeleteForm::class,
    ],
    'permission_provider' => EntityPermissionProvider::class,
    'route_provider' => [
      'default' => DefaultHtmlRouteProvider::class,
    ],
  ],
  links: [
    'collection' => '/farm/settings/asset_link/default_plugin/list',
    'add-form' => '/farm/settings/asset_link/default_plugin/add',
    'edit-form' => '/farm/settings/asset_link/default_plugin/{asset_link_default_plugin}/edit',
    'delete-form' => '/farm/settings/asset_link/default_plugin/{asset_link_default_plugin}/delete',
    'enable' => '/farm/settings/asset_link/default_plugin/{asset_link_default_plugin}/enable',
    'disable' => '/farm/settings/asset_link/default_plugin/{asset_link_default_plugin}/disable',
  ],
  admin_permission: 'administer farm settings',
  label_count: [
    'singular' => '@count Asset Link default plugin',
    'plural' => '@count Asset Link default plugins',
  ],
  config_export: [
    'id',
    'url',
    'sidebarUrlPattern',
    'user_defined',
    'status',
  ],
)]
class AssetLinkDefaultPlugin extends ConfigEntityBase implements ConfigEntityInterface {

  /**
   * The plugin id.
   *
   * @var string
   */
  protected $id;

  /**
   * The plugin URL.
   *
   * @var string
   */
  protected $url;

  /**
   * The URL whitelist regex for which pages the sidebar loads on.
   *
   * @var string
   */
  protected $sidebarUrlPattern;

  /**
   * The plugin status.
   *
   * @var bool
   */
  protected $status;

  /**
   * Whether the plugin was user defined (not provided by a farmOS module).
   *
   * @var bool
   */
  protected $user_defined;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->url();
  }

  /**
   * {@inheritdoc}
   */
  public function setId($id) {
    $this->id = $id;
  }

  /**
   * {@inheritdoc}
   */
  public function url() {
    return $this->url;
  }

  /**
   * {@inheritdoc}
   */
  public function sidebarUrlPattern() {
    return $this->sidebarUrlPattern;
  }

  /**
   * {@inheritdoc}
   */
  public function userDefined() {
    return $this->user_defined;
  }

  /**
   * {@inheritdoc}
   */
  public function setUserDefined($user_defined) {
    $this->user_defined = $user_defined;
  }

}
