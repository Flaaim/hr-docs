<?php

namespace App\Http\Exception;

class SubscriptionPlanAlreadyUpgradedException extends \LogicException
{
    public function __construct($message = "Subscription plan already exists", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
