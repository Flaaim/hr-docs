<?php

namespace App\Http\Exception\Payment;

use Throwable;

class PaymentCreateFailedException extends \LogicException
{
    public function __construct($message = "Ошибка создания платежа", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
