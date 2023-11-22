<?php

namespace App\Http\Middleware;

use Service\Auth\AuthInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class OnlyAuthenticated
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Callable $next
     * @return mixed|Response
     */
    public function __invoke(
        Request $request,
        Response $response,
        $next
    ) {
        if (!$this->getAuth()->isAuthenticated()) {
            return $response->withRedirect('/auth/login');
        }

        return $next($request, $response);
    }

    private function getAuth(): AuthInterface
    {
        return $this->container->get('auth');
    }
}
