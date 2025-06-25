<?php

namespace App\Http\Exception\Payment;

use Throwable;

class PaymentWebhookException extends \LogicException
{
    public function __construct($message = "Webhook handling failed", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
