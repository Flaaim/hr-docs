<?php

namespace App\Http\Queue\Handlers\Email;

use App\Http\Queue\Messages\Email\EmailVerificationMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AsMessageHandler]
class EmailVerificationHandler
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger, Environment $twig){
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->twig = $twig;
    }
    public function handle(EmailVerificationMessage $message): void
    {
        try{
            $email = (new Email())
                ->to($message->email)
                ->subject($message->subject)
                ->html(
                    $this->twig->render('emails/welcome.html.twig', [
                        'email' => $message->email,
                        'verifyToken' => $message->verifyToken,
                        'subject' => $message->subject,
                    ])
                );

            $this->mailer->send($email);
        }catch (TransportExceptionInterface $e) {
            $this->logger->error('Transport failed'. $e->getMessage());
        }catch (\Exception $e){
            $this->logger->error("Failed to send verification email: " . $e->getMessage());
            throw $e;
        }
    }
}
