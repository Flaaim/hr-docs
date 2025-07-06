<?php

namespace Tests\Unit\Subscription;

use App\Http\Exception\Subcription\SubscriptionPlanNotFoundException;
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
        $this->subscriptionService = $this->getMockBuilder(SubscriptionService::class)
            ->setConstructorArgs([
                $this->mockSubscription,
                $this->mockSubscriptionPlan
            ])
            ->onlyMethods(['downgradeToFreePlan']) // Мокируем только этот метод
            ->getMock();
    }

    public function testUpgradeToMonthlyPlanFailed_emptyPlan(): void
    {
        $this->mockSubscriptionPlan->method('getMonthlyPlan')->willReturn([]);
        $this->expectException(SubscriptionPlanNotFoundException::class);
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

    public function testIsSubscriptionExpired()
    {
        $this->assertFalse($this->subscriptionService->isSubscriptionExpired(
            []
        ));
        $this->assertTrue($this->subscriptionService->isSubscriptionExpired(
            ['ends_at' => '2025-06-18']
        ));
    }

    public function testCheckAndUpdateSubscriptionWithExpiredPlan()
    {
        $this->mockSubscription->method('getCurrentPlan')->willReturn(['ends_at' => '2020-01-01']);

        $this->subscriptionService->expects($this->once())
            ->method('downgradeToFreePlan')
            ->with(1);


        $this->subscriptionService->checkAndUpdateSubscription(1);
    }

    public function testCheckAndUpdateSubscriptionWithActivePlan()
    {
        $this->mockSubscription->method('getCurrentPlan')->willReturn(['ends_at' => '2030-08-01']);
        $this->subscriptionService->expects($this->never())->method('downgradeToFreePlan');

        $this->subscriptionService->checkAndUpdateSubscription(1);
    }
}
