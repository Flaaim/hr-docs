<?php

namespace App\Http\Exception\Mail;

class MailNotSendException extends \LogicException
{
    public function __construct($message = "Ошибка отправки письма", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
