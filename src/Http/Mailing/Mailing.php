<?php

namespace App\Http\Mailing;

use App\Http\Models\BaseModel;

class Mailing extends BaseModel
{
    const TABLE_NAME = 'mailing_user_list';

    public function setUserMailingList(int $user_id): void
    {
        $this->database->insert(self::TABLE_NAME, ['user_id' => $user_id]);
    }
    public function getUsersMailingList(): array
    {
        $queryBuilder = $this->database->createQueryBuilder()
            ->select('mul.user_id, u.email, mul.is_unsubscribed, mul.subscription_date')
            ->from(self::TABLE_NAME, 'mul')
            ->leftJoin('mul', 'users', 'u', 'mul.user_id = u.id');

        return $queryBuilder->fetchAllAssociative() ?: [];
    }
}
