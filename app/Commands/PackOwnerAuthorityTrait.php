<?php

declare(strict_types=1);

namespace Commands;

use Admin\App;

trait PackOwnerAuthorityTrait
{
    public function isAuthorizedForCurrentUser(): bool
    {
        $currentUser = App::getInstance()->getAuth()->getUser();

        $pack = null;
        if (property_exists($this, 'context')) {
            $pack = $this->context?->getPack();
        }

        return $pack ? $currentUser?->owned($pack) : false;
    }
}
