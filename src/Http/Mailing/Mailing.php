<?php

namespace App\Http\Mailing;

use App\Http\Models\BaseModel;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;

class Mailing extends BaseModel
{
    const TABLE_NAME = 'mailing_user_list';

    public function setUserMailingList(int $user_id): void
    {
        $this->database->insert(self::TABLE_NAME, ['user_id' => $user_id]);
    }
    public function getAll(array $filters = []): array
    {
        $queryBuilder = $this->database->createQueryBuilder()
            ->select('mul.user_id, u.email, mul.is_unsubscribed, mul.subscription_date')
            ->from(self::TABLE_NAME, 'mul')
            ->leftJoin('mul', 'users', 'u', 'mul.user_id = u.id');

        foreach ($filters as $field => $value) {
            if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $field)) {
                continue;
            }
            $queryBuilder->andWhere("$field = :$field")
                ->setParameter($field, $value);
        }

        return $queryBuilder->fetchAllAssociative() ?: [];
    }

    public function getByOnlySubscription(int $is_unsubscribed): array
    {
        $result = $this->getAll(['is_unsubscribed' => $is_unsubscribed]);
        return $result ?: [];
    }
    public function updateMailingList(array $users): void
    {
        $user_ids = array_column($users, 'user_id');
        $queryBuilder = $this->database->createQueryBuilder();
        $queryBuilder
            ->update(self::TABLE_NAME)
            ->set('send_count', 'send_count + 1')
            ->set('last_sent_date', ':last_sent_date')
            ->where($queryBuilder->expr()->in('user_id', ':user_ids'))
            ->setParameter('last_sent_date', (new \DateTimeImmutable())->format('Y-m-d H:i:s'))
            ->setParameter('user_ids', $user_ids, ArrayParameterType::INTEGER);

        $queryBuilder->executeStatement();

    }

    public function unsubscribe(int $user_id): int
    {
        return $this->database->update(self::TABLE_NAME, ['is_unsubscribed' => 1], ['user_id' => $user_id, 'is_unsubscribed' => 0]);
    }
}
