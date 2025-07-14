<?php

namespace App\Http\Queue\Handlers\Email;

use App\Http\Queue\Messages\Email\EmailVerificationMessage;
use App\Http\Services\Mail\Mail;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EmailVerificationHandler
{
    private Mail $mail;
    private LoggerInterface $logger;

    public function __construct(Mail $mail, LoggerInterface $logger){
        $this->mail = $mail;
        $this->logger = $logger;
    }

    public function handle(EmailVerificationMessage $message): void
    {
        try{
            $this->mail->setTo($message->email)
                ->setSubject('Подтверждение регистрации на сайте')
                ->setBodyFromTemplate('emails/welcome.html.twig',
                    [
                        'email' => $message->email,
                        'link' => $_ENV['APP_PATH'].'/auth/verify?token='.$message->verifyToken,
                    ])
            ->send();
        }catch (\Exception $e){
            $this->logger->error("Failed to send verification email: " . $e->getMessage());
            throw $e;
        }
    }
}
