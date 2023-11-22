<?php
/** @var $app \Admin\App */

use App\Http\Controller\AuthController;
use App\Http\Controller\BranchesController;
use App\Http\Controller\CommandsController;
use App\Http\Controller\ConfigController;
use App\Http\Controller\GitController;
use App\Http\Controller\PacksController;
use App\Http\Controller\ProjectsController;
use App\Http\Controller\UsersController;
use App\Http\Middleware\OnlyAuthenticated;

// NEW ROUTES HERE!
$app->map(['GET', 'POST'], '/auth/login[/]', [AuthController::class, 'login']);
$app->map(['GET', 'POST'], '/auth/register[/]', [AuthController::class, 'register']);

$app->group('', function(\Admin\App $app) {
    $app->get('/auth/logout[/]', [AuthController::class, 'logout']);

    $app->get('/[user[/]]', [UsersController::class, 'index']);
    $app->map(['GET', 'POST'], '/user/addkey[/]', [UsersController::class, 'addkey']);
    $app->map(['GET', 'POST'], '/user/personal-access-token[/]', [UsersController::class, 'accessToken']);
    $app->map(['GET', 'POST'], '/user/committer-data[/]', [UsersController::class, 'committerInfo']);
    $app->map(['GET', 'POST'], '/user/check-token', [UsersController::class, 'checkToken']);

    $app->get('/projects[/]', [ProjectsController::class, 'index']);
    $app->get('/projects/{id:[0-9]+}[/]', [ProjectsController::class, 'show']);
    $app->get('/projects/create-new[/]', [ProjectsController::class, 'showCreateForm']);
    $app->get('/projects/save[/]', [ProjectsController::class, 'store']);
    $app->get('/projects/{id:[0-9]+}/fetch[/]', [ProjectsController::class, 'fetch']);

    $app->get('/packs/{id}[/]', [PacksController::class, 'show']);

    $app->get('/git[/]', [GitController::class, 'index']);
    $app->get('/git/update[/]', [GitController::class, 'update']);
    $app->get('/git/checkout[/]', [GitController::class, 'checkout']);
    $app->get('/git/reset[/]', [GitController::class, 'reset']);

    $app->get('/git/add-repository[/]', [GitController::class, 'showAddRepositoryForm']);
    $app->post('/git/add-repository[/]', [GitController::class, 'addRepository']);

    $app->get('/branches/add/{projectId:[0-9]+}/{packId:[0-9]+}[/]', [BranchesController::class, 'addBranches']);
    $app->get('/branches/remove/{projectId:[0-9]+}/{packId:[0-9]+}[/]', [BranchesController::class, 'removeBranches']);
    $app->get('/branches/fork-pack/{projectId:[0-9]+}/{packId:[0-9]+}[/]', [BranchesController::class, 'forkPack']);
    $app->get('/branches/create-pack/{projectId:[0-9]+}[/]', [BranchesController::class, 'createPack']);
    $app->post('/branches/save/{projectId:[0-9]+}[/]', [BranchesController::class, 'savePack']);

    $app->get('/config[/]', [ConfigController::class, 'index']);
    $app->get('/config/edit[/]', [ConfigController::class, 'edit']);
    $app->get('/config/settings[/]', [ConfigController::class, 'settings']);

    $app->get('/commands/apply[/]', [CommandsController::class, 'apply']);
})->add(OnlyAuthenticated::class);

//// OLD COMMON ROUTE FOR ALL
$app->any('/{module}[/[{controller}[/[{action}[/[{id}]]]]]]', function ($request, $response, $args) use ($app) {
    $callable = [$app, 'doRoute'];

    return call_user_func($callable, ...$args);
});
