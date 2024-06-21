<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\View\MenuItem;
use Illuminate\Support\Facades\Auth;

class NavigationComposer
{
    public function compose(\Illuminate\View\View $view): void
    {
        $view->with([
            'mainMenu' => $this->loadMenu(),
            'breadcrumbs' => [],
        ]);
    }

    protected function loadMenu(): array
    {
        $menu = [];

        $dashboardItem = new MenuItem('Releases', '/releases');
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
            array_push($menu, $itemLogin);

            $itemRegister = new MenuItem('Sign Up', '/sign-up');
            $itemRegister->setIconClass('fa-solid fa-user-plus');
            array_push($menu, $itemRegister);
        }

        return $menu;
    }
}