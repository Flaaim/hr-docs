<?php

namespace App\Http\Exception\Payment;

use Throwable;

class PaymentInfoException extends \LogicException
{
    public function __construct($message = "Ошибка при получении информации о платеже", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
