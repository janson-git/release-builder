<?php

declare(strict_types=1);

namespace Service;

use eftec\bladeone\BladeOne;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class ErrorHandler
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, \Throwable $exception)
    {
        $code = $exception->getCode() > 0 ? $exception->getCode() : 500;

        if ($request->isXhr()) {
            return $response
                ->withStatus($code)
                ->withJson([
                    'code' => $code,
                    'reason' => 'Internal Server Error',
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTrace(),
                ]);
        }

        /** @var BladeOne $renderer */
        $renderer = $this->container->get('blade');

        $output = $renderer->run('./error.blade.php', [
            'code' => $code,
            'reason' => 'Internal Server Error',
            'exception' => $exception,
        ]);

        return $response
            ->withStatus($code)
            ->write($output);
    }
}
