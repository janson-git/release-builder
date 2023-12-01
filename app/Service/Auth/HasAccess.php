<?php

declare(strict_types=1);

namespace Service\Auth;

use Service\User;

/**
 * Trait to checking current user for access/authorization/ownership for entities
 */
trait HasAccess
{
    /**
     * Checks that user owned the $entity
     * @param $entity
     * @return bool
     */
    public function owned($entity): bool
    {
        if (!method_exists($entity, 'getUser') || $entity->getUser() === null) {
            return false;
        }

        /** @var User $this */
        return $this->getId() === $entity->getUser()->getId();
    }
}
