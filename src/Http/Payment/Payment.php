<?php

namespace App\Http\Payment;

use App\Http\Models\BaseModel;

class Payment extends BaseModel
{
    const TABLE_NAME = 'payments';

    public function getAll(array $filters = []): array
    {
        $queryBuilder = $this->database->createQueryBuilder()
            ->select('p.id, p.yookassa_id, p.amount, p.status, p.created_at, u.email')->from(self::TABLE_NAME, 'p')
            ->leftJoin('p', 'users', 'u', 'p.user_id = u.id')
            ->leftJoin('p', 'subscription_plans', 'sp', 'p.plan_slug = sp.slug')
            ->orderBy('p.created_at','desc');

        foreach ($filters as $field => $value) {
            $queryBuilder->andWhere("$field = :$field")
                ->setParameter($field, $value);
        }
        return $queryBuilder->fetchAllAssociative() ?: [];
    }

    public function savePayment(array $paymentData): int
    {
        return $this->database->insert(self::TABLE_NAME, $paymentData);
    }
    public function updatePaymentStatus(string $payment_id, string $status): int
    {
        return $this->database->update(self::TABLE_NAME, ['status' => $status], ['yookassa_id' => $payment_id]);
    }

    public function findByPaymentId(string $paymentId): array
    {
        $result = $this->database->fetchAssociative('SELECT * FROM ' . self::TABLE_NAME . ' WHERE payment_id = ?', [$paymentId]);
        return $result ?: [];
    }

    public function deletePayment(int $payment_id): int
    {
        return $this->database->delete(self::TABLE_NAME, ['id' => $payment_id]);
    }
}
