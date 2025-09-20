<?php

namespace App\Http\Queue\Handlers\Email;

use App\Http\Queue\Messages\Email\EmailResetMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailResetHandler
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly Environment $twig,
    )
    {}
    public function handle(EmailResetMessage $message): void
    {
        try{
            $email = (new Email())
                ->to($message->email)
                ->subject($message->subject)
                ->html(
                  $this->twig->render('emails/reset.html.twig', ['token' => $message->token, 'subject' => $message->subject])
                );
                $this->mailer->send($email);
        }catch (TransportExceptionInterface $e) {
            $this->logger->error("Transport error: " . $e->getMessage());
        }catch (\Exception $e){
            $this->logger->error("Failed to send reset password email: " . $e->getMessage() . "\n" . $e->getLine() . "\n" . $e->getFile());
            throw $e;
        }

    }
}
