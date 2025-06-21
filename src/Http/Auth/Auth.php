<?php

namespace App\Http\Auth;

use App\Http\Models\BaseModel;
use App\Http\Subscription\Subscription;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;


class Auth extends BaseModel
{
    private const TABLE_NAME = 'users';
    private const SOCIAL_ACCOUNTS_TABLE = 'social_accounts';
    private const USERS_CONFIRMATION_TABLE = 'users_confirmations';
    private const USERS_RESETS_TABLE = 'users_resets';
    private const REMEMBER_TOKENS_TABLE = 'remember_tokens';
    private const VERIFIED_USER = 1;
    public function __construct(
        Connection $database,
        private readonly Subscription $subscription,
        private readonly LoggerInterface $logger
    )
    {
        parent::__construct($database);
    }
    public function findByEmail(string $email): array
    {
        $result = $this->database->fetchAssociative(
            "SELECT id, email, role, password_hash, created_at FROM ". self::TABLE_NAME. " WHERE email = :email", ['email' => $email]
        );
        return $result ?: [];
    }
    public function findById(int $id): array
    {
        $result = $this->database->fetchAssociative(
            "SELECT id, email, role, password_hash, created_at FROM ". self::TABLE_NAME. " WHERE id = :id", ['id' => $id]
        );
        return $result ?: [];
    }
    public function findVerifyToken($token): array|false
    {
        return $this->database->fetchAssociative(
            "SELECT id, user_id, token, expires FROM ". self::USERS_CONFIRMATION_TABLE ." WHERE token = ? AND expires >= UNIX_TIMESTAMP()",
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
            $this->logger->warning('Ошибка создания пользователя', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    public function createUserBySocial(string $email): int
    {
        try{
            $this->database->beginTransaction();
            $this->database->insert(self::TABLE_NAME, [
                'email' => $email,
                'verified' => self::VERIFIED_USER,
                'password_hash' => null
            ]);
            $userId = $this->database->lastInsertId();
            $this->subscription->setFreePlan($userId);
            $this->database->commit();
            return $userId;
        }catch (\Exception $e) {
            $this->database->rollBack();
            $this->logger->warning('Ошибка создания пользователя через социальные сети', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function createUserConfirmation(int $userId, string $token): void
    {
        $this->database->insert(self::USERS_CONFIRMATION_TABLE, [
            'user_id' => $userId,
            'token' => $token,
            'expires' => (new DateTimeImmutable())->modify('+1 day')->getTimestamp()

        ]);
    }

    public function createReset(int $userId, string $token, int $expires): int|string
    {
        return $this->database->insert(self::USERS_RESETS_TABLE, [
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
        $this->database->delete(self::REMEMBER_TOKENS_TABLE, ['user_id' => $user_id]);

        $this->database->insert(self::REMEMBER_TOKENS_TABLE, [
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
            ->from(self::REMEMBER_TOKENS_TABLE, 't')
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
        return $this->database->delete(self::REMEMBER_TOKENS_TABLE, [
            'token' => hash('sha256', $token)
        ]);
    }

    public function findBySocialId(string $provider, string $social_id): array
    {
        $queryBuilder = $this->database->createQueryBuilder()->select("u.*")->from(self::TABLE_NAME, 'u')
            ->innerJoin('u', 'social_accounts', 'sa', 'u.id = sa.user_id')->where("sa.provider = :provider")
            ->andWhere("sa.social_id = :social_id")
            ->setParameter("provider", $provider)
            ->setParameter("social_id", $social_id);

        return $queryBuilder->fetchAssociative() ?: [];
    }

    public function addSocialAccount(int $user_id, string $provider, string $social_id): void
    {
        $this->database->insert(self::SOCIAL_ACCOUNTS_TABLE, [
            'user_id' => $user_id,
            'provider' => $provider,
            'social_id' => $social_id,
            'created_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s')
        ]);
    }
}
