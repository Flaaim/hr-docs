<?php

namespace App\Http\Queue\Messages\Email;

class EmailResetMessage
{
    public function __construct(
        public string $email,
        public string $subject,
        public string $token
    ){}
}
