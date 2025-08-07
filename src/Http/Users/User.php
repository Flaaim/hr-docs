<?php

namespace App\Http\Users;

use App\Http\Models\BaseModel;
use DateTimeImmutable;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;

class User extends BaseModel
{
    const TABLE_NAME = 'users';
    private const USERS_CONFIRMATION_TABLE = 'users_confirmations';

    public function getAll(array $filters = []): array
    {
        $queryBuilder = $this->database->createQueryBuilder()
            ->select('u.id, u.email, u.verified, u.created_at, s.plan_id,  s.downloads_remaining, s.ends_at, sp.name, sp.slug')
            ->from(self::TABLE_NAME, 'u')
            ->leftJoin('u', 'subscriptions', 's', 'u.id = s.user_id')
            ->leftJoin('s', 'subscription_plans', 'sp', 's.plan_id = sp.id')
            ->orderBy('u.created_at', 'desc');

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

    public function deleteExpiredUsers(): int
    {
        $expirationTime = (new \DateTimeImmutable())->modify('-1 day')->getTimestamp();
        try{
            $this->database->beginTransaction();
            $tokens = $this->database->createQueryBuilder()
                ->select('uc.token')->from(self::USERS_CONFIRMATION_TABLE, 'uc')
                ->join('uc', self::TABLE_NAME, 'u', 'uc.user_id = u.id')
                ->where('uc.expires < :expirationTime')
                ->setParameter('expirationTime', $expirationTime)
                ->executeQuery()
                ->fetchFirstColumn();

            $usersDeleted = $this->database->createQueryBuilder()
                ->delete(self::TABLE_NAME)
                    ->where('verified = :verified')
                    ->Andwhere('id IN (SELECT user_id FROM '. self::USERS_CONFIRMATION_TABLE .' WHERE expires < :expirationTime)')
                    ->setParameter('expirationTime', $expirationTime)
                    ->setParameter('verified', 0)
                    ->executeStatement();

            if(!empty($tokens)){
                $this->database->createQueryBuilder()
                    ->delete(self::USERS_CONFIRMATION_TABLE)
                    ->where('token IN (:tokens)')
                    ->setParameter('tokens', $tokens, ArrayParameterType::STRING)
                    ->executeStatement();
            }
            $this->database->commit();
            return $usersDeleted;
        }catch (\Exception $e) {
            $this->database->rollBack();
            throw $e;
        }

    }

}
