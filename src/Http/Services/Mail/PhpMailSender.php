<?php

namespace App\Http\Services\Mail;

use App\Http\Exception\Mail\MailNotSendException;
use App\Http\Interface\MailSenderInterface;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class PhpMailSender implements MailSenderInterface
{
    private PHPMailer $mail;
    public function __construct(PHPMailer $mailer)
    {
        $this->mail = $mailer;

        $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
        $this->mail->isSMTP();
        $this->mail->CharSet = 'UTF-8';
        $this->mail->isHTML();
        $this->mail->Host = $_ENV['MAIL_HOST'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $_ENV['MAIL_USERNAME'];
        $this->mail->Password = $_ENV['MAIL_PASSWORD'];
        $this->mail->SMTPSecure = 'ssl';
        $this->mail->Port = 465;
        $this->mail->setLanguage('ru');
        $this->mail->setFrom($_ENV['MAIL_USERNAME'], $_ENV['APP_HOST']);
    }

    public function send(string $email, string $subject, string $message): bool
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->Subject = $subject;
            $this->mail->Body = $message;
            $this->mail->addAddress($email);
            return $this->mail->send();
        }  catch (PHPMailerException  $e) {
            throw new MailNotSendException($e->getMessage());
        }
    }
}
