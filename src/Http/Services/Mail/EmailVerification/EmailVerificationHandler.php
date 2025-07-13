<?php

namespace App\Http\Services\Mail\EmailVerification;

use App\Http\Services\Mail\Mail;
use Psr\Log\LoggerInterface;

class EmailVerificationHandler
{
    public function __construct(private readonly Mail $mail, private readonly LoggerInterface $logger){}

    public function handle(EmailVerificationMessage $message): void
    {
        try{
            $this->mail->setTo($message->email)
                ->setSubject('Подтверждение регистрации на сайте')
                ->setBodyFromTemplate('emails/welcome.html.twig',
                    [
                        'email' => $message->email,
                        'token' => $message->verifyToken
                    ])
            ->send();
            $this->logger->info("Email sent to {$message->email}");
        }catch (\Exception $e){
            $this->logger->error("Failed to send email: " . $e->getMessage());
            throw $e;
        }
    }
}
