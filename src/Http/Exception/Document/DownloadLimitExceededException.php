<?php

namespace App\Http\Exception\Document;

use App\Http\Exception\Throwable;

class DownloadLimitExceededException extends \LogicException
{
    public function __construct($message = "Пробные попытки закончились", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
