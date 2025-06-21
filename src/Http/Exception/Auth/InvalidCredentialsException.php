<?php

namespace App\Http\Exception\Auth;

use Throwable;

class InvalidCredentialsException extends \LogicException
{
    public function __construct($message = "Invalid credentials", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
