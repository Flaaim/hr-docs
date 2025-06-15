<?php

namespace App\Http\Services\Mail;

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
        $this->body = $this->twig->render($templateName, $data);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function send(): bool
    {
        return $this->sender->send($this->to, $this->subject, $this->body);
    }
}
