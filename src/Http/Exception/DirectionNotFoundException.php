<?php

namespace App\Http\Exception;

class DirectionNotFoundException extends \LogicException
{
    public function __construct($message = "Directions not found", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
