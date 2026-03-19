<?php

namespace Drupal\farmos_asset_link\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defines FarmAssetLinkController class.
 */
class FarmAssetLinkController extends ControllerBase {

  public function __construct(
    protected RequestStack $requestStack,
    protected FileSystemInterface $fileSystem,
    ModuleHandlerInterface $module_handler,
  ) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * Top-level handler for asset link requests.
   */
  public function content() {
    $request = $this->requestStack->getCurrentRequest();
    $path = $request->getPathInfo();

    if (strpos($path, '/alink') !== 0) {
      $response = new Response();
      $response->setStatusCode(400);
      $response->headers->set('Content-Type', 'text/plain');
      $response->setContent("This controller is only intended to serve paths under /alink");
      return $response;
    }

    // This should never happen since a compliant web server should handle
    // sending a 400 for requests with relative paths, but since it could be a
    // pretty bad security vulnerability if this ever got through we'll defend
    // against it.
    // https://datatracker.ietf.org/doc/html/rfc7230#section-5.3.1
    if (strpos($path, '../') !== FALSE) {
      $response = new Response();
      $response->setStatusCode(400);
      $response->headers->set('Content-Type', 'text/plain');
      $response->setContent("This controller is only intended to serve paths under /alink");
      return $response;
    }

    $module_base_path = $this->fileSystem->realpath($this->moduleHandler->getModule('farmos_asset_link')->getPath());

    $asset_link_dist_path = "$module_base_path/asset-link-dist/";

    // Remove the '/alink' prefix.
    $path_suffix = substr($path, 6);

    // Some paths under /alink should return an HTTP 404 instead of just
    // returning the index.html content.
    $require_file_exists = FALSE;
    $resource_dirs = [
      '/css/',
      '/fonts/',
      '/img/',
      '/js/',
      '/plugins/',
      '/sidecar/',
    ];
    foreach($resource_dirs as $resource_dir) {
      if (strpos($path_suffix, $resource_dir) === 0) {
        $require_file_exists = TRUE;
        break;
      }
    }

    $file_path = $asset_link_dist_path . $path_suffix;

    if (strpos($path_suffix, '/plugins/~') === 0) {
        $modulePluginPathSuffix = substr($path_suffix, 10);

        $modulePluginCfgIdEndPos = strpos($modulePluginPathSuffix, '.alink.');

        if ($modulePluginCfgIdEndPos <= 0) {
            return $this->textError(400, "Invalid module plugin URL. Must contain the plugin name followed by .alink.{ext}");
        }

        $modulePluginCfgId = substr($modulePluginPathSuffix, 0, $modulePluginCfgIdEndPos);

        $storage = $this->entityTypeManager->getStorage('asset_link_default_plugin');

        $defaultPluginConfig = $storage->load($modulePluginCfgId);

        if (empty($defaultPluginConfig)) {
            return $this->textError(404, "Unknown Asset Link Plugin config '$modulePluginCfgId'");
        }

        $matches = [];
        if (!preg_match('/^\{module:(?P<module>[^\}]+)\}/', $defaultPluginConfig->url(), $matches)) {
            return $this->textError(400, "Asset Link Plugin is not module scoped '{$defaultPluginConfig->url()}'");
        }

        $moduleName = $matches['module'];

        $module_base_path = $this->fileSystem->realpath($this->moduleHandler->getModule($moduleName)->getPath());

        $file_path = $module_base_path . '/' . $modulePluginPathSuffix;
    }

    if ($require_file_exists && !file_exists($file_path)) {
      return $this->textError(404, "No such file '$file_path'");
    }

    if (empty($path_suffix) || $path_suffix === '/' || !file_exists($file_path)) {
      $file_path = $asset_link_dist_path . "index.html";
    }

    $content_type = $this->getContentMimetype($file_path);

    $response_content = file_get_contents($file_path);

    // The file being served should always exist because when doesn't, we fall
    // back on serving the `index.html` file. If that doesn't exist it probably
    // means our front-end artifacts aren't available so return an HTTP 500 -
    // our fault.
    if ($response_content === FALSE) {
      return $this->textError(500, "Could not find Asset Link front-end resources - if this is happening in a development environment, make sure ./alinkjs has been built.");
    }

    $base_path = base_path();

    $response_content = str_replace('/__THIS_GETS_REPLACED_AT_RUNTIME_BY_THE_DRUPAL_CONTROLLER__/', $base_path . 'alink/', $response_content);

    $response = new Response();
    $response->setStatusCode(200);

    $cookie_expiry_epoch_sec = time() + (14 * 24 * 60 * 60);

    $cookie = Cookie::create('assetLinkDrupalBasePath', $base_path, $expire = $cookie_expiry_epoch_sec, $path = '/', $domain = NULL, $secure = FALSE, $httpOnly = FALSE, $raw = TRUE, $sameSite = 'Lax');
    $response->headers->setCookie($cookie);

    $response->headers->set('Content-Type', $content_type);
    $response->setContent($response_content);
    return $response;
  }

  // roughly based on https://developer.mozilla.org/en-US/docs/Learn/Server-side/Node_server_without_framework
  private function getContentMimetype($file_path) {
    $extname = pathinfo($file_path, PATHINFO_EXTENSION);

    $mime_types = [
      'html' => 'text/html',
      'js' => 'text/javascript',
      'css' => 'text/css',
      'json' => 'application/json',
      'png' => 'image/png',
      'jpg' => 'image/jpg',
      'gif' => 'image/gif',
      'svg' => 'image/svg+xml',
      'wav' => 'audio/wav',
      'mp4' => 'video/mp4',
      'woff' => 'application/font-woff',
      'ttf' => 'application/font-ttf',
      'eot' => 'application/vnd.ms-fontobject',
      'otf' => 'application/font-otf',
      'wasm' => 'application/wasm',
    ];

    return $mime_types[$extname] ?? 'application/octet-stream';
  }

  private function textError($code, $msg) {
    $response = new Response();
    $response->setStatusCode($code);
    $response->headers->set('Content-Type', 'text/plain');
    $response->setContent($msg);
    return $response;
  }

}
