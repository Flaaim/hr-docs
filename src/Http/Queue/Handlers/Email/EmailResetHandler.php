<?php

namespace App\Http\Queue\Handlers\Email;

use App\Http\Queue\Messages\Email\EmailResetMessage;
use App\Http\Services\Mail\Mail;
use Psr\Log\LoggerInterface;

class EmailResetHandler
{
    private Mail $mail;
    private LoggerInterface $logger;
    public function __construct(Mail $mail, LoggerInterface $logger)
    {
        $this->mail = $mail;
        $this->logger = $logger;
    }
    public function handle(EmailResetMessage $message): void
    {
        try{
            $this->mail->setTo($message->email)
                ->setSubject('Запрос сброса пароля')
                ->setBodyFromTemplate(
                    'emails/reset.html.twig',
                    ['link' => $_ENV['APP_PATH'].'/auth/reset?token='.$message->token]
                )
                ->send();
        }catch (\Exception $e){
            $this->logger->error("Failed to send reset password email: " . $e->getMessage());
            throw $e;
        }

    }
}
