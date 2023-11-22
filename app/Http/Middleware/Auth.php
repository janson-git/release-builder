<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Admin\App;
use Service\Data;
use Service\User;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class Auth
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
        // check sessions and users by session
        $sessionToken = $request->getCookieParam('tkn');

        $sessions = Data::scope(App::DATA_SESSIONS)->getAll();

        if (array_key_exists($sessionToken, $sessions)) {
            $user = User::getByLogin($sessions[$sessionToken]);
            if ($user !== null) {
                $this->getAuth()->setUser($user);
            }
        }

        return $next($request, $response);
    }

    private function getAuth(): \User\Auth
    {
        return $this->container->get('auth');
    }
}
