<?php

namespace Subscription;

use App\Http\Exception\SubscriptionPlanAlreadyUpgradedException;
use App\Http\Exception\SubscriptionPlanNotFoundException;
use App\Http\Subscription\Subscription;
use App\Http\Subscription\SubscriptionPlan;
use App\Http\Subscription\SubscriptionService;
use PHPUnit\Framework\TestCase;

class SubscriptionServiceTest extends TestCase
{
    private Subscription $mockSubscription;
    private SubscriptionPlan $mockSubscriptionPlan;
    private SubscriptionService $subscriptionService;
    private array $mounthly = ['id' => '1', 'user_id' => '1', 'plan_id' => '2'];
    private array $current_plan = ['id' => '1', 'plan_id' => '1'];
    public function setUp(): void
    {
        $this->mockSubscription =$this->createMock(Subscription::class);
        $this->mockSubscriptionPlan = $this->createMock(SubscriptionPlan::class);
        $this->subscriptionService = new SubscriptionService(
            $this->mockSubscription,
            $this->mockSubscriptionPlan
        );
    }

    public function testUpgradeToMonthlyPlanFailed_emptyPlan(): void
    {
        $this->mockSubscriptionPlan->method('getMonthlyPlan')->willReturn([]);
        $this->expectException(SubscriptionPlanNotFoundException::class);
        $this->subscriptionService->upgradeToMonthlyPlan(1);
    }

    public function testUpgradeToMonthlyPlanFailed_planAlreadyUpgraded(): void
    {
        $this->mockSubscriptionPlan->method('getMonthlyPlan')->willReturn($this->mounthly);
        $this->mockSubscription->method('getCurrentPlan')->willReturn($this->current_plan);
        $this->expectException(SubscriptionPlanAlreadyUpgradedException::class);
        $this->subscriptionService->upgradeToMonthlyPlan(1);
    }

    public function testUpgradeToMonthlyPlanSuccess(): void
    {
        $this->mockSubscriptionPlan->method('getMonthlyPlan')->willReturn($this->mounthly);
        $this->mockSubscription->method('getCurrentPlan')->willReturn(['id' => '1', 'plan_id' => '2']);
        $this->mockSubscription->expects($this->once())->method('updatePlan');
        $this->subscriptionService->upgradeToMonthlyPlan(1);
    }

    public function testUpgradePlanFailed()
    {
        $slug = 'incorrect_slug';
        $this->expectException(SubscriptionPlanNotFoundException::class);
        $this->subscriptionService->upgradeToMonthlyPlan(1, $slug);
    }

    public function testNeedsPlanUpdate_True()
    {
        $this->mockSubscription->method('getCurrentPlan')->willReturn(['plan_id' => 1]);
        $this->mockSubscriptionPlan->method('getPlanBySlug')->willReturn(['id' => 2]);
        $this->assertTrue($this->subscriptionService->needsPlanUpdate(1, 'some_slug'));
    }

    public function testNeedsPlanUpdate_False()
    {
        $this->mockSubscription->method('getCurrentPlan')->willReturn(['plan_id' => 1]);
        $this->mockSubscriptionPlan->method('getPlanBySlug')->willReturn(['id' => 1]);
        $this->assertFalse($this->subscriptionService->needsPlanUpdate(1, 'some_slug'));
    }

}
