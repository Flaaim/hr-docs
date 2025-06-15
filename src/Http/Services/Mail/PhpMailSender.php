<?php

namespace App\Http\Services\Mail;

use App\Http\Interface\MailSenderInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class PhpMailSender implements MailSenderInterface
{
    private PhpMailer $mail;
    public function __construct()
    {
        $this->mail = new PHPMailer(true);
    }

    public function send(string $email, string $subject, string $message): bool
    {
        try {
            $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
            $this->mail->isSMTP();
            $this->mail->CharSet = 'UTF-8';
            $this->mail->isHTML();
            $this->mail->Host = 'mail.hosting.reg.ru';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'admin@hr-doc.ru';
            $this->mail->Password = '158754429asD!';
            $this->mail->SMTPSecure = 'ssl';
            $this->mail->Port = 465;
            $this->mail->setLanguage('ru');
            $this->mail->setFrom('admin@ot-doc.ru', 'hr-doc.ru');
            $this->mail->Subject = $subject;
            $this->mail->Body = $message;
            $this->mail->addAddress($email);
            return $this->mail->send();
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }
}
