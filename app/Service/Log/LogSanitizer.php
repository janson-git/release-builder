<?php

declare(strict_types=1);

namespace Service\Log;

use Psr\Container\ContainerInterface;

/**
 * Helper to clean logs from security sensitive information
 */
class LogSanitizer
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function sanitize(string $message): string
    {
        $user = $this->container->get('auth')->getUser();
        if ($user !== null) {
            $token = $user->getAccessToken();
            $message = str_replace($token, "<user-personal-token>", $message);
        }

        return $message;
    }
}
