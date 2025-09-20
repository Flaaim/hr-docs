<?php

namespace App\Http\Mailing;

use App\Http\Auth\Auth;
use App\Http\Queue\Messages\Email\SendUpdateMessage;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBus;

class MailingService
{
    public function __construct(
        private readonly Mailing $mailing,
        private readonly MessageBus $messageBus,
        private readonly LoggerInterface $logger,
        private readonly Auth $auth
    )
    {}
    public function getAll(): array
    {
        return $this->mailing->getAll();
    }
    public function getOnlyActiveUsers(): array
    {
        return $this->mailing->getByOnlySubscription($is_unsubscribed = 0);
    }

    public function sendUpdates(array $users, string $text, string $subject): void
    {
        if(empty($users)){
            throw new \InvalidArgumentException('No active users found.');
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($users as $user) {
            if (empty($user['email'])) {
                $this->logger->warning('User missing email', ['user' => $user]);
                $errorCount++;
                continue;
            }
            try{
                $this->messageBus->dispatch(new SendUpdateMessage(
                    $user['email'],
                    $text,
                    $subject,
                    $this->generateUnsubscribeToken($user['email'])
                ));
                $successCount++;
            }catch (\Throwable $e){
                $this->logger->error("Failed to dispatch email to {$user['email']}", ['message' => $e->getMessage()]);
                $errorCount++;
            }
        }

        $this->logger->info('Mailing dispatch completed', [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'total' => count($users)
        ]);
        if($successCount > 0){
            $this->mailing->updateMailingList($users);
        }

    }
    public function unsubscribeUser(string $email): bool
    {

        if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
            throw new \InvalidArgumentException("Invalid email address.");
        }
        $user = $this->auth->findByEmail($email);
        if(empty($user)){
            return true;
        }
        try{
            $affected = $this->mailing->unsubscribe($user['id']);

            $this->logger->info('User unsubscribed', [
                'email' => $email,
                'user_id' => $user['id'],
                'timestamp' => time()
            ]);

            return $affected > 0;
        }catch (\Exception $e){
            $this->logger->error('Unsubscribe failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    public function generateUnsubscribeToken(string $email): string
    {
        $secret = $_ENV['UNSUBSCRIBE_SECRET'] ?? 'fallback-secret';
        $expires = time() + (7 * 24 * 3600);

        $data = [
            'email' => $email,
            'expires' => $expires
        ];

        $payload = base64_encode(json_encode($data));
        $signature = hash_hmac('sha256', $payload, $secret);

        return $payload . '.' . $signature;
    }

    public function verifyUnsubscribeToken(string $token): string
    {
        $secret = $_ENV['UNSUBSCRIBE_SECRET'] ?? 'fallback-secret';

        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException('Invalid token format.');
        }
        list($payload, $signature) = $parts;

        // Проверяем подпись
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        if (!hash_equals($expectedSignature, $signature)) {
            throw new InvalidArgumentException('Invalid token signature.');
        }

        $data = json_decode(base64_decode($payload), true);

        // Проверяем срок действия
        if ($data['expires'] < time()) {
            throw new InvalidArgumentException('Token expired.');
        }

        return $data['email'];
    }
}
