<?php

namespace App\Http\Exception\Document;

use App\Http\Exception\Throwable;

class FileNotFoundInStorageException extends \LogicException
{
    public function __construct($message = "Файл не найден в хранилище", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
