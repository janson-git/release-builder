<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Service\Log\Logger;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class HandleRouteCallable
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        /** @var $route \Slim\Route */
        $route = $request->getAttribute('route');

        $callable = $route->getCallable();

        if (is_string($callable)) {
            list($class, $action) = explode(':', $callable);
        } elseif (is_array($callable)) {
            list($class, $action) = $callable;
        }

        if (isset($class)) {
            $controller = new $class($this->container, $request, $response);

            // set charged callable for route!
            $route->setCallable([$controller, $action]);
            $request = $request->withAttribute('route', $route);

            /** @var Logger $logger */
            $logger = $this->container->get('logger');
            $logger->debug('Routing to: '.get_class($controller).'->'.$action, [__METHOD__]);
        }

        $response = $next($request, $response);

        return $response;
    }
}
