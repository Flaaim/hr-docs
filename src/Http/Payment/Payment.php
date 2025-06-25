<?php

namespace App\Http\Payment;

use App\Http\Models\BaseModel;

class Payment extends BaseModel
{
    const TABLE_NAME = 'payments';

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
}
