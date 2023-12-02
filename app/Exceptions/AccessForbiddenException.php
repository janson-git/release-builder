<?php

declare(strict_types=1);

namespace Exceptions;

class AccessForbiddenException extends \Exception
{
    protected $message = 'Forbidden';
    protected $code = \Slim\Http\StatusCode::HTTP_FORBIDDEN;
}
