<?php

namespace App\Http\Queue\Handlers\Email;

use App\Http\Queue\Messages\Email\SendUpdateMessage;
use App\Http\Services\Mail\Mail;
use Psr\Log\LoggerInterface;

class SendUpdateHandler
{
    public function __construct(
        private readonly Mail $mail,
        private readonly LoggerInterface $logger
    ){}

    public function handle(SendUpdateMessage $message): void
    {
        try {
            $this->mail->setTo($message->email)
                ->setSubject($message->subject)
                ->setBodyFromTemplate(
                    'emails/update.html.twig', ['text' => $message->text])
                ->send();

        } catch (\Exception $e) {
            $this->logger->error("Failed to send update email: " . $e->getMessage());
            throw $e;
        }
    }
}
