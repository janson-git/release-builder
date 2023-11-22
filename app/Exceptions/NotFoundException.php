<?php

declare(strict_types=1);

namespace Exceptions;

class NotFoundException extends \Exception
{
    protected $message = 'Not found';
    protected $code = \Slim\Http\StatusCode::HTTP_NOT_FOUND;
}
