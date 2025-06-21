<?php

namespace App\Http\Exception\Auth;

use App\Http\Exception\Throwable;

class SocialAuthException extends \LogicException
{
    public function __construct($message = "Social auth error", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
