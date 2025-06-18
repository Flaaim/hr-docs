<?php

namespace App\Http\Users;

use App\Http\Models\BaseModel;

class User extends BaseModel
{
    const TABLE_NAME = 'users';
    public function getAll(array $filters = []): array
    {
        $queryBuilder = $this->database->createQueryBuilder()
            ->select('u.id, u.email, u.verified, u.created_at, s.plan_id,  s.downloads_remaining, s.ends_at, sp.name, sp.slug')
            ->from(self::TABLE_NAME, 'u')
            ->leftJoin('u', 'subscriptions', 's', 'u.id = s.user_id')
            ->leftJoin('s', 'subscription_plans', 'sp', 's.plan_id = sp.id')
            ->orderBy('u.created_at','desc');

        foreach ($filters as $field => $value) {
            $queryBuilder->andWhere("$field = :$field")
                ->setParameter($field, $value);
        }
        return $queryBuilder->fetchAllAssociative() ?: [];
    }

    public function getBySubscription(int $subscriptionId): array
    {
        $result = $this->getAll(['plan_id' => $subscriptionId]);
        return $result ?: [];
    }

    public function getById(int $id): array
    {
        return $this->database->createQueryBuilder()
            ->select('u.id, u.email, u.verified, u.created_at, s.plan_id, s.downloads_remaining, s.ends_at, sp.name')
            ->from(self::TABLE_NAME, 'u')
            ->leftJoin('u', 'subscriptions', 's', 'u.id = s.user_id')
            ->leftJoin('s', 'subscription_plans', 'sp', 's.plan_id = sp.id')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative() ?: [];
    }
    public function confirmUser(int $user_id): int
    {
        return $this->database->update(self::TABLE_NAME, ['verified' => 1], ['id' => $user_id]);
    }


}
