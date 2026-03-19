<?php

namespace Drupal\farmos_asset_link\EventSubscriber;

use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Route all requests under /alink/* to the farmos_asset_link controller.
 */
class RequestSubscriber implements EventSubscriberInterface  {

  public function __construct(
    protected RouteProviderInterface $routeProvider,
    protected ControllerResolverInterface $controllerResolver,
    protected ArgumentResolverInterface $argumentResolver,
  ) {}

  /**
   * The request event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event.
   */
  public function checkAppRequest(RequestEvent $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo();

    // Let '/alink/backend' requests fall through to the
    // normal routing since those will have their own controllers.
    if (strpos($path, '/alink/backend') === 0) {
      return;
    }

    // Otherwise, redirect all requests that start with "/alink" requests to a
    // single route. Note: this is necessary because core doesn't have any other
    // way to really handle "wildcard/catch all" routes.
    if (strpos($path, '/alink') === 0) {
      $route = $this->routeProvider->getRouteByName('farmos_asset_link.content');
      $definition = $route->getDefault('_controller');
      $controller = $this->controllerResolver->getControllerFromDefinition($definition, $path);
      $arguments = $this->argumentResolver->getArguments($request, $controller);
      $response = \call_user_func_array($controller, $arguments);
      if ($response instanceof Response) {
        // Set the response, necessary so the kernel knows it got something
        // which will also prevent any other event handler from running.
        $event->setResponse($response);
        return;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Check for /app requests.
    // Based on https://drupal.stackexchange.com/a/284722
    $events[KernelEvents::REQUEST][] = ['checkAppRequest', 1000];
    return $events;
  }

}
