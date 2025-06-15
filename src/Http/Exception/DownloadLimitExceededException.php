<?php

namespace App\Http\Exception;

class DownloadLimitExceededException extends \LogicException
{
    public function __construct($message = "Пробные попытки закончились", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
