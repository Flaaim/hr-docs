<?php

namespace App\Http\Auth;

use App\Http\Models\BaseModel;
use App\Http\Subscription\Subscription;
use App\Http\Subscription\SubscriptionService;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;

class Auth extends BaseModel
{
    private Subscription $subscription;
    public function __construct(Connection $database, Subscription $subscription)
    {
        parent::__construct($database);
        $this->subscription = $subscription;

    }
    public function findByEmail(string $email): array
    {
        $result = $this->database->fetchAssociative(
            'SELECT id, email, role, password_hash, created_at FROM users WHERE email = ?',
            [$email]
        );
        return $result ?: [];
    }
    public function findVerifyToken($token): array|false
    {
        return $this->database->fetchAssociative(
            'SELECT id, user_id, token, expires FROM users_confirmations WHERE token = ? AND expires >= UNIX_TIMESTAMP()',
            [$token]
        );
    }
    public function createUser(string $email, string $password, string $token): bool
    {
        try{
            $this->database->beginTransaction();

            $this->database->insert('users', [
                'email' => $email,
                'password_hash' => $password,
            ]);
            $userId = $this->database->lastInsertId();
            $this->subscription->setFreePlan($userId);
            $this->createUserConfirmation($userId, $token);
            $this->database->commit();
            return true;
        }catch (\Exception $e) {
            $this->database->rollBack();
            //Добавить тут логгирование
            return false;
        }
    }

    private function createUserConfirmation(int $userId, string $token): void
    {
        $this->database->insert('users_confirmations', [
            'user_id' => $userId,
            'token' => $token,
            'expires' => (new DateTimeImmutable())->modify('+1 day')->getTimestamp()

        ]);
    }

    public function createReset(int $userId, string $token, int $expires): int|string
    {
        return $this->database->insert('users_resets', [
            'user_id' => $userId,
            'token' => $token,
            'expires' => $expires
        ]);
    }

    public function markUserAsVerified(int $user_id): void
    {
        $this->database->update('users', ['verified' => 1], ['id' => $user_id]);
    }
}
