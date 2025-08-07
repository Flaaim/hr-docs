<?php

namespace App\Http\Users;

use App\Http\Auth\Auth;
use App\Http\Exception\Auth\UserNotFoundException;
use App\Http\Subscription\Subscription;
use App\Http\Subscription\SubscriptionService;
use InvalidArgumentException;

class UserService
{
    private Subscription $subscription;
    private SubscriptionService $subscriptionService;
    private User $user;
    private Auth $auth;

    public function __construct(Subscription $subscription, SubscriptionService $subscriptionService, User $user, Auth $auth)
    {
        $this->subscription = $subscription;
        $this->subscriptionService = $subscriptionService;
        $this->user = $user;
        $this->auth = $auth;
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
    public function clearUserExpiredRegistrations(): int
    {
        return $this->user->deleteExpiredUsers();
    }
}
