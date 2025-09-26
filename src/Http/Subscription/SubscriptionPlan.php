<?php

namespace App\Http\Subscription;

use App\Http\Models\BaseModel;
use Doctrine\DBAL\Connection;

class SubscriptionPlan extends BaseModel
{
    const TABLE_NAME = 'subscription_plans';
    const FREE_PLAN_SLUG = 'free';
    const MONTHLY_PLAN_SLUG = 'monthly';
    const YEARLY_PLAN_SLUG = 'annual';
    const ETERNAL_PLAN_SLUG = 'eternal';
    public function getFreePlan(): array
    {
        return $this->getPlanBySlug(self::FREE_PLAN_SLUG);
    }
    public function getMonthlyPlan(): array {
        return $this->getPlanBySlug(self::MONTHLY_PLAN_SLUG);
    }

    public function getYearlyPlan(): array {
        return $this->getPlanBySlug(self::YEARLY_PLAN_SLUG);
    }

    public function getEternalPlan(): array {
        return$this->getPlanBySlug(self::ETERNAL_PLAN_SLUG);
    }

    public function all(): array
    {
        $result = $this->database->fetchAllAssociative("SELECT * FROM " . self::TABLE_NAME);
        return $result ?: [];
    }
    public function getPlanBySlug(string $slug): array
    {
        $result = $this->database->fetchAssociative(
            "SELECT * FROM " . self::TABLE_NAME . " WHERE slug = ?",
            [$slug]
        );
        return $result ?: [];
    }


}
