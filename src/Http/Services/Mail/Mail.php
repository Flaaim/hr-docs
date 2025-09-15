<?php

namespace App\Http\Services\Mail;

use App\Http\Exception\Mail\MailNotSendException;
use App\Http\Interface\MailInterface;
use App\Http\Interface\MailSenderInterface;
use Twig\Environment;

class Mail implements MailInterface
{
    private string $to;
    private string $subject;
    private string $body;
    private MailSenderInterface $sender;
    private Environment $twig;
    public function __construct(MailSenderInterface $sender, Environment $twig)
    {
        $this->sender = $sender;
        $this->twig = $twig;
    }

    /**
     * @inheritDoc
     */
    public function setTo(string $email): MailInterface
    {
        $this->to = $email;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSubject(string $subject): MailInterface
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setBody(string $body): MailInterface
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setBodyFromTemplate(string $templateName, array $data): MailInterface
    {
        if(empty($data)){
            throw new MailNotSendException('Data для отправки email пуста');
        }
        $this->body = $this->twig->render($templateName, $data);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function send(): void
    {
        if(empty($this->to)){
            throw new MailNotSendException('Не указан получатель письма');
        }
        if(empty($this->subject)){
            throw new MailNotSendException('Не указана тема письма');
        }
        if(empty($this->body)){
            throw new MailNotSendException('Не указано содержимое письма');
        }
        $this->sender->send($this->to, $this->subject, $this->body);
    }
}
