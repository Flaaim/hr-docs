<?php

namespace App\Http\Exception;

use Throwable;

class UserNotFoundException extends \LogicException
{
    public function __construct($message = "Auth not found", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
