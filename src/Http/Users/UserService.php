<?php

namespace App\Http\Users;

use App\Http\Exception\SubscriptionPlanNotFoundException;
use App\Http\Exception\UserNotFoundException;
use App\Http\Subscription\Subscription;
use App\Http\Subscription\SubscriptionPlan;
use App\Http\Subscription\SubscriptionService;
use InvalidArgumentException;

class UserService
{
    private Subscription $subscription;
    private SubscriptionService $subscriptionService;

    public function __construct(Subscription $subscription, SubscriptionService $subscriptionService)
    {
        $this->subscription = $subscription;
        $this->subscriptionService = $subscriptionService;
    }
    public function editUser(array $data): void
    {
        if(empty($data)){
            throw new InvalidArgumentException('Data to edit user is empty');
        }
        if($this->subscriptionService->needsPlanUpdate($data['user_id'], $data['plan_slug'])){
            $this->subscriptionService->upgradePlan($data['user_id'], $data['plan_slug']);
        }

    }
}
