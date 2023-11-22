<?php

use App\Http\Middleware;
use Exceptions\AuthException;
use Psr\Container\ContainerInterface;

chdir(__DIR__);

if ($_SERVER['REQUEST_URI'] === '/ping') {
    echo 'PHP_OK';
    exit(0);
}

const DEBUG_MODE = true;
const ROOT_DIR = __DIR__;
const SSH_KEYS_DIR = __DIR__ . '/ssh_keys';
const STORAGE_DIR = __DIR__ . '/storage';
const LOGS_DIR = __DIR__ . '/logs';
const REPOS_DIR = __DIR__ . '/storage/repos';
const SANDBOX_DIR = __DIR__ . '/storage/sandbox';

require_once('app/helpers.php');
require_once('debug.php');
ini_set('date.timezone', 'UTC');

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require 'vendor/autoload.php';
$loader->add('', 'app/');
$loader->addPsr4('App\\', 'app/');

$app = new \Admin\App([
    'settings' => [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => true,
    ],
    'foundHandler' => function () {
        // Converts request params to arguments for controller methods
        return new \Admin\ArgumentsToActionStrategy();
    },
    'cookies' => function (ContainerInterface $container) {
        return new \Service\Util\CookiesPipe();
    },
    'logger' => function (ContainerInterface $container) {
        return new \Service\Log\Logger(
            new \Service\Log\LogSanitizer($container)
        );
    },
    'errorHandler' => function (ContainerInterface $container) {
        return new \Service\ErrorHandler($container);
    },
    'phpErrorHandler' => function (ContainerInterface $container) {
        return new \Service\ErrorHandler($container);
    },
//    'notAllowedHandler' => function (ContainerInterface $container) {
//        return new \Service\ErrorHandler($container);
//    },
//    'notFoundHandler' => function (ContainerInterface $container) {
//        return new \Service\ErrorHandler($container);
//    },
    'blade' => function () {
        return new \eftec\bladeone\BladeOne(
            './app/Http/View',
            STORAGE_DIR . '/cache/compiles'
        );
    },
    'view' => function (ContainerInterface $container) {
        return new \Admin\View($container);
    },
    'auth' => function (ContainerInterface $container) {
        return new \User\Auth();
    },
]);

try {
    // BASIC AUTH
    if (env('HTTP_BASIC_AUTH')) {
        $hosts = env('HTTP_BASIC_AUTH_HOSTS', "localhost, 127.0.0.1");
        $hosts = array_map('trim', explode(',', $hosts));

        if (!env('HTTP_BASIC_AUTH_USER') || !env('HTTP_BASIC_AUTH_PASS')) {
            throw new AuthException('Failed to setup auth credentials for basic auth', 403);
        }

        $app->add(new \Slim\Middleware\HttpBasicAuthentication([
            "users" => [
                env('HTTP_BASIC_AUTH_USER') => env('HTTP_BASIC_AUTH_PASS'),
            ],
            "relaxed" => $hosts,
        ]));
    }

    // COMMON APP MIDDLEWARES
    $app->add(Middleware\Auth::class);
    $app->add(Middleware\HandleRouteCallable::class);

    $app->loadRoutes();

    $app->run();

} catch (\Exception $e) {
    $response = $app->getResponse();

    $container = $app->getContainer();
    $bladeRenderer = $container->get('blade');

    $output = $bladeRenderer->run('./error.blade.php', [
        'code' => $response->getStatusCode(),
        'reason' => $response->getReasonPhrase(),
        'exception' => $e,
    ]);

    $response = $response
        ->withStatus($e->getCode() ?: \Slim\Http\StatusCode::HTTP_INTERNAL_SERVER_ERROR)
        ->write($output);

    $app->respond($response);
} finally {
    $app->terminate();
}
