<?php

namespace App\Http\Mailing;

use App\Http\Queue\Messages\Email\SendUpdateMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBus;

class MailingService
{
    public function __construct(
        private readonly Mailing $mailing,
        private readonly MessageBus $messageBus,
        private readonly LoggerInterface $logger
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

    public function sendUpdates(array $users, string $text, string $name): void
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
                    $name
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
}
