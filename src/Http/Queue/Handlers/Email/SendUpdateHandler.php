<?php

namespace App\Http\Queue\Handlers\Email;

use App\Http\Queue\Messages\Email\SendUpdateMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class SendUpdateHandler
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly Environment $twig,
    ){}

    public function handle(SendUpdateMessage $message): void
    {
        try {
            $email = (new Email())
                ->to($message->email)
                ->subject($message->subject)
                ->html(
                    $this->twig->render('emails/update.html.twig', [
                        'text' => $message->text,
                        'subject' => $message->subject,
                        'token' => $message->token
                    ])
                );
                $this->mailer->send($email);

        } catch (TransportExceptionInterface $e) {
            $this->logger->error("Transport error: " . $e->getMessage());
        }catch (\Exception $e) {
            $this->logger->error("Failed to send update email: " . $e->getMessage());
            throw $e;
        }
    }
}
