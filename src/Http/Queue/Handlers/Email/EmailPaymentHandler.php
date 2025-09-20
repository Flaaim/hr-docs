<?php

namespace App\Http\Queue\Handlers\Email;

use App\Http\Queue\Messages\Email\EmailPaymentMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailPaymentHandler
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;
    private Environment $twig;
    public function __construct(MailerInterface $mailer, LoggerInterface $logger, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->twig = $twig;
    }
    public function handle(EmailPaymentMessage $message)
    {
        $email = (new Email())
                ->to($message->email)
                ->subject($message->subject)
                ->html(
                    $this->twig->render('emails/payment.html.twig',
                        [
                            'email' => $message->email,
                            'subject' => $message->subject,
                            'amount' => $message->amount,
                            'slug' => $message->slug
                        ])
                    );
        try{
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e->getMessage());
        }catch (\Exception $e){
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }
}
