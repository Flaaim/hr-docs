<?php

namespace App\Http\Auth;

use App\Http\Models\BaseModel;
use App\Http\Subscription\Subscription;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;

class Auth extends BaseModel
{
    const TABLE_NAME = 'users';
    private Subscription $subscription;
    public function __construct(Connection $database, Subscription $subscription)
    {
        parent::__construct($database);
        $this->subscription = $subscription;

    }
    public function findByEmail(string $email): array
    {
        $result = $this->database->fetchAssociative(
            "SELECT id, email, role, password_hash, created_at FROM ". self::TABLE_NAME. " WHERE email = :email", ['email' => $email]
        );
        return $result ?: [];
    }
    public function findVerifyToken($token): array|false
    {
        return $this->database->fetchAssociative(
            "SELECT id, user_id, token, expires FROM users_confirmations WHERE token = ? AND expires >= UNIX_TIMESTAMP()",
            [$token]
        );
    }
    public function createUser(string $email, string $password, string $token): bool
    {
        try{
            $this->database->beginTransaction();

            $this->database->insert(self::TABLE_NAME, [
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
        $this->database->update(self::TABLE_NAME, ['verified' => 1], ['id' => $user_id]);
    }

    public function saveRememberToken(int $user_id, string $token, DateTimeImmutable $date): void
    {
        $this->database->delete('remember_tokens', ['user_id' => $user_id]);

        $this->database->insert('remember_tokens', [
            'user_id' => $user_id,
            'token' => hash('sha256', $token),
            'expires_at' => $date->format('Y-m-d H:i:s')
        ]);
    }

    public function findByToken(string $token): array
    {
        $hashed_token = hash('sha256', $token);
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $queryBuilder = $this->database->createQueryBuilder()->select("u.id, u.email, u.role, u.created_at, token, expires_at")
            ->from('remember_tokens', 't')
            ->leftJoin("t", "users", "u", "t.user_id = u.id")
            ->where("t.token = :token")
            ->andwhere("t.expires_at > :now")
            ->setParameter("token", $hashed_token)
            ->setParameter("now", $now);

        $result = $queryBuilder->fetchAssociative();
        return $result ?: [];
    }

    public function deleteRememberToken(string $token): int
    {
        return $this->database->delete('remember_tokens', [
            'token' => hash('sha256', $token)
        ]);
    }
}
