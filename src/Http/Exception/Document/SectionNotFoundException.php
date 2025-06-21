<?php

namespace App\Http\Exception\Document;

use App\Http\Exception\Throwable;

class SectionNotFoundException extends \LogicException
{
    public function __construct($message = "Sections not found", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
