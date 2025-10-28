<?php

namespace App\Http\Subscription;

use App\Http\Models\BaseModel;
use Doctrine\DBAL\Connection;

class Subscription extends BaseModel
{
    const TABLE_NAME = 'subscriptions';
    const UNLIMITED_DOWNLOADS = null;
    const DOWNLOAD_LIMIT = 0;
    const FREE_PLAN_ID = 1;
    public function setFreePlan(int $user_id): void
    {
        $this->database->insert(self::TABLE_NAME, [
            'user_id' => $user_id,
            'plan_id' => self::FREE_PLAN_ID,
            'downloads_remaining' => self::DOWNLOAD_LIMIT,
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at' => null
        ]);
    }


    public function updatePlan(int $user_id, array $plan): void
    {
        $this->database->update(
            self::TABLE_NAME,
        [
            'plan_id' => $plan['id'],
            'downloads_remaining' => $plan['downloads_limit'],
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at' => $plan['duration_days'] ? date('Y-m-d H:i:s', strtotime("+{$plan['duration_days']} days")) : null,
        ],
        ['user_id' => $user_id]);
    }

    public function getCurrentPlan(int $user_id): array
    {
        $queryBuilder = $this->database->createQueryBuilder()->select('s.plan_id, s.downloads_remaining, s.starts_at, s.ends_at, p.name, p.duration_days, p.description')->from(self::TABLE_NAME, 's')
            ->leftJoin('s', 'subscription_plans', 'p', 's.plan_id = p.id')
            ->where('s.user_id = :user_id')->setParameter('user_id', $user_id);
        return $queryBuilder->fetchAssociative() ?: [];
    }

    public function decrementDownloads(int $user_id, int $downloads): void
    {
        $this->database->update(self::TABLE_NAME, [
            'downloads_remaining' => $downloads,
        ], ['user_id' => $user_id]);
    }
}
