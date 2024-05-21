<?php

declare(strict_types=1);

namespace Exceptions;

class UnauthorizedException extends \Exception
{
    protected $message = 'You are not authorized for this action';
    protected $code = \Slim\Http\StatusCode::HTTP_UNAUTHORIZED;
}
