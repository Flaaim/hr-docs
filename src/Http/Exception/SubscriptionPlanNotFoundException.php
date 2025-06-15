<?php

namespace App\Http\Exception;

class SubscriptionPlanNotFoundException extends \LogicException
{
    public function __construct($message = "Subscription plan not found", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
