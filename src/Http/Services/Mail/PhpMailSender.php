<?php

namespace App\Http\Services\Mail;

use App\Http\Exception\Mail\MailNotSendException;
use App\Http\Interface\MailSenderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class PhpMailSender implements MailSenderInterface
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly LoggerInterface $logger
    )
    {}

    public function send(string $email, string $subject, string $message): void
    {
        $message = (new Email())
            ->subject($subject)
            ->to($email)
            ->html($message);
        try{
            $this->mailer->send($message);
        }catch (MailNotSendException $e){
            $this->logger->error('Unable to send message',  [$e->getMessage()]);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Transport error',  [$e->getMessage()]);
        }
    }
}
