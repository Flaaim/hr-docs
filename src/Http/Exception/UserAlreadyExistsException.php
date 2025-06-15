<?php

namespace App\Http\Exception;

use Throwable;

class UserAlreadyExistsException extends \LogicException
{
    public function __construct($message = "Auth already exists", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
