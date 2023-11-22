<?php

namespace Service\Util;

use \Slim\Http\Response;
use \Slim\Http\Request;

class CookiesPipe
{
    public function deleteCookie(Response $response, string $key): Response
    {
        $cookie = urlencode($key) . '='
            . '; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0; path=/; secure; httponly';
        return $response->withAddedHeader('Set-Cookie', $cookie);
    }

    public function addCookie(
        Response $response,
        string $cookieName,
        string $cookieValue,
        int $lifetimeSeconds = null,
        string $path = '/',
        bool $httponly = true
    ): Response {
        $lifetimeOneYear = 60 * 60 * 24 * 365;
        $lifetime = $lifetimeSeconds ?? $lifetimeOneYear;

        $expiry = new \DateTimeImmutable('now + ' . $lifetime . ' seconds');

        $cookie = urlencode($cookieName) . '=' . urlencode($cookieValue)
            . '; expires=' . ($expire ?? $expiry->format(\DateTime::COOKIE))
            . '; Max-Age=' . $lifetime
            . '; path=/; secure; httponly';

        return $response->withAddedHeader('Set-Cookie', $cookie);
    }

    public function getCookieValue(Request $request, string $cookieName): ?string
    {
        $cookies = $request->getCookieParam($cookieName);
        return $cookies[$cookieName] ?? null;
    }
}
