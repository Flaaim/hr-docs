<?php

namespace App\Http\Exception;

class SectionNotFoundException extends \LogicException
{
    public function __construct($message = "Sections not found", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
