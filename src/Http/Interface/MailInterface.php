<?php

namespace App\Http\Interface;

use Twig\Environment;

interface MailInterface
{
    public function __construct(MailSenderInterface $sender, Environment $twig);
    /**
     * @param string $email
     * @return self
     */
    public function setTo(string $email): self;

    /**
     * @param string $subject
     * @return self
     */
    public function setSubject(string $subject): self;

    /**
     * @param string $body
     * @return self
     */
    public function setBody(string $body): self;

    /**
     * @param string $templateName
     * @param array $data
     * @return self
     */
    public function setBodyFromTemplate(string $templateName, array $data): self;

    /**
     * @return bool
     */
    public function send(): bool;
}
