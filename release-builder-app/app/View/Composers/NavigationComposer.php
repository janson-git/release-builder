<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Models\Release;
use App\Models\Sandbox;
use App\View\Breadcrumbs;
use App\View\MenuItem;
use Illuminate\Support\Facades\Auth;

class NavigationComposer
{
    public function compose(\Illuminate\View\View $view): void
    {
        $view->with([
            'mainMenu' => $this->loadMenu(),
            'breadcrumbs' => $this->loadBreadcrumbs(),
        ]);
    }

    protected function loadMenu(): array
    {
        $menu = [];

        $dashboardItem = new MenuItem('Releases', '/releases', [
            '#/releases/\d*#',
            '#/sandboxes/\d*#',
        ]);
        $dashboardItem->setIconClass('fa-solid fa-table-columns');
        $menu[] = $dashboardItem;

        $gitItem = new MenuItem('Services', '/services', [
            '/services',
            '/services/add',
        ]);
        $gitItem->setIconClass('fa-solid fa-code-branch');
        $menu[] = $gitItem;

        if (Auth::user()) {
            $itemProfile = new MenuItem('Profile', '/user', [
                '/',
                '#/user#',
            ]);
            $itemProfile->setIconClass('fa-solid fa-user');
            $menu[] = $itemProfile;
        } else {
            $itemLogin = new MenuItem('Log In', '/login');
            $itemLogin->setIconClass('fa-solid fa-right-to-bracket');
            $menu[] = $itemLogin;

            $itemRegister = new MenuItem('Sign Up', '/sign-up');
            $itemRegister->setIconClass('fa-solid fa-user-plus');
            $menu[] = $itemRegister;
        }

        return $menu;
    }

    protected function loadBreadcrumbs(): Breadcrumbs
    {
        /** @var Breadcrumbs $breadcrumbs */
        $breadcrumbs = app(Breadcrumbs::class);

        $route = \Route::current();
        $id = $route->parameter('id');
        $parts = explode('/', $route->uri);

        switch ($parts[0]) {
            case 'releases':
                if (!$id && !isset($parts[1])) {
                    $breadcrumbs->push('Releases');
                } elseif (!$id && isset($parts[1])) {
                    // For example - new release page: /releases/create
                    $breadcrumbs
                        ->push('Releases', '/releases')
                        ->push(ucfirst($parts[1]));
                } elseif ($id) {
                    $breadcrumbs->push('Releases', '/releases');

                    $release = Release::find($id);
                    if (isset($parts[2])) {
                        $breadcrumbs
                            ->push($release->name, "/releases/{$id}")
                            ->push(ucfirst($parts[2]));
                    } else {
                        $breadcrumbs->push($release->name);
                    }
                }
                return $breadcrumbs;

            case 'services':
                $breadcrumbs->push('Services');
                return $breadcrumbs;

            case 'sandboxes':
                if ($id) {
                    $sandbox = Sandbox::find($id);
                    $release = $sandbox->release;
                    $sandboxTitle = "Sandbox #{$id} - {$sandbox->service->repository_name}";

                    $breadcrumbs
                        ->push('Releases', '/releases')
                        ->push($release->name, "/releases/{$sandbox->release_id}");

                    if (isset($parts[2])) {
                        $breadcrumbs
                            ->push($sandboxTitle, "/sandboxes/{$id}")
                            ->push(ucfirst($parts[2]));
                    } else {
                        $breadcrumbs->push($sandboxTitle);
                    }
                }
                return $breadcrumbs;

            case 'user':
                if (isset($parts[1])) {
                    $breadcrumbs
                        ->push('User', '/user')
                        ->push(ucfirst($parts[1]));
                } else {
                    $breadcrumbs->push('User');
                }
                return $breadcrumbs;
        }

        return $breadcrumbs;
    }
}
