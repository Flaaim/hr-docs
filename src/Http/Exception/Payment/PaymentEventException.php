<?php

namespace App\Http\Exception\Payment;

use Throwable;

class PaymentEventException extends \LogicException
{
    public function __construct($message = "Ошибка обработки платежа", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
