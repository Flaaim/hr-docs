<?php

namespace App\Http\Exception\Document;

use App\Http\Exception\Throwable;

class DocumentWrongTypeException extends \LogicException
{
    public function __construct($message = "Неподдерживаемый тип файла", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
