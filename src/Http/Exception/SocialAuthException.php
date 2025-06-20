<?php

namespace App\Http\Exception;

class SocialAuthException extends \LogicException
{
    public function __construct($message = "Social auth error", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
