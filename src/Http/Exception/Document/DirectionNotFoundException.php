<?php

namespace App\Http\Exception\Document;

use App\Http\Exception\Throwable;

class DirectionNotFoundException extends \LogicException
{
    public function __construct($message = "Directions not found", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
