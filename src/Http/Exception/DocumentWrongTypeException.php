<?php

namespace App\Http\Exception;

class DocumentWrongTypeException extends \LogicException
{
    public function __construct($message = "Неподдерживаемый тип файла", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
