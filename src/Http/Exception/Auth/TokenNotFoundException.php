<?php

namespace App\Http\Exception\Auth;

use App\Http\Exception\Throwable;

class TokenNotFoundException extends \LogicException
{
    public function __construct($message = "Token not found", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
