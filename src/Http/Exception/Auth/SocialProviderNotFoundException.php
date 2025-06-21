<?php

namespace App\Http\Exception\Auth;

use Throwable;

class SocialProviderNotFoundException extends \LogicException
{
    public function __construct($message = "Provider not found", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
