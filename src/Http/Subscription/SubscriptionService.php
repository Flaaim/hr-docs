<?php

namespace App\Http\Subscription;

use App\Http\Exception\SubscriptionPlanAlreadyUpgradedException;
use App\Http\Exception\SubscriptionPlanExistsException;
use App\Http\Exception\SubscriptionPlanNotFoundException;
use InvalidArgumentException;
use PHPUnit\Framework\Exception;
use RuntimeException;

class SubscriptionService
{
    private SubscriptionPlan $plan;
    private Subscription $subscription;

    public function __construct(Subscription $subscription, SubscriptionPlan $plan)
    {
        $this->subscription = $subscription;
        $this->plan = $plan;
    }
    public function upgradePlan($user_id, $slug): void
    {
        switch ($slug){
            case "monthly":
                $this->upgradeToMonthlyPlan($user_id);
            break;
            case "annual":
                $this->upgradeToYearlyPlan($user_id);
            break;
            case "free":
                $this->downgradeToFreePlan($user_id);
            break;
            default:
                throw new SubscriptionPlanNotFoundException('Subscription plan not found');
        }
    }
    public function upgradeToMonthlyPlan(int $user_id): void
    {
        try{
            $plan = $this->plan->getMonthlyPlan();
            if(empty($plan)){
                throw new SubscriptionPlanNotFoundException('Plan not found');
            }
            $current_plan = $this->subscription->getCurrentPlan($user_id);
            if($current_plan['plan_id'] === $plan['id']){
                throw new SubscriptionPlanAlreadyUpgradedException('План уже обновлен');
            }
            $this->subscription->updatePlan($user_id, $plan);
        }catch (\RuntimeException $e){
            throw new RuntimeException(
                "Failed to upgrade user {$user_id} to monthly plan: " . $e->getMessage()
            );
        }

    }
    public function upgradeToYearlyPlan(int $user_id): void
    {
        try{
            $plan = $this->plan->getYearlyPlan();
            if(empty($plan)){
                throw new SubscriptionPlanNotFoundException('Plan not found');
            }
            $current_plan = $this->subscription->getCurrentPlan($user_id);
            if($current_plan['plan_id'] === $plan['id']){
                throw new SubscriptionPlanAlreadyUpgradedException('Plan already upgraded');
            }

            $this->subscription->updatePlan($user_id, $plan);
        }catch (\RuntimeException $e){
            throw new RuntimeException(

                "Failed to upgrade user {$user_id} to yearly plan: " . $e->getMessage()
            );
        }
    }

    public function downgradeToFreePlan(int $user_id): void
    {
        try {
            $plan = $this->plan->getFreePlan();
            if(empty($plan)){
                throw new SubscriptionPlanNotFoundException('Plan not found');
            }
            $this->subscription->updatePlan($user_id, $plan);
        }catch (\RuntimeException $e){
            throw new RuntimeException(
                "Failed to downgrade user {$user_id} to free plan: " . $e->getMessage()
            );
        }
    }
}
