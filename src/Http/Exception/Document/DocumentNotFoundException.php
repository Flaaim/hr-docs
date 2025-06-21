<?php

namespace App\Http\Exception\Document;

use App\Http\Exception\Throwable;

class DocumentNotFoundException extends \LogicException
{
    public function __construct($message = "Documents not found", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
