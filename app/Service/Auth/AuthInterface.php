<?php

namespace Service\Auth;

interface AuthInterface
{
    public function isAuthenticated(): bool;
}
