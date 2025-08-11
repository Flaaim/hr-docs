<?php

namespace App\Http\Exception\Auth;

use Throwable;

class InvalidStateException extends \LogicException
{
    public function __construct($message = "Invalid state ", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
