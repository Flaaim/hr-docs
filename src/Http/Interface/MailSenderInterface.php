<?php

namespace App\Http\Interface;

interface MailSenderInterface
{
    public function send(string $email, string $subject, string $message): void;
}
