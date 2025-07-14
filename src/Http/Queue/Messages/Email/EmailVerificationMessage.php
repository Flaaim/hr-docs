<?php

namespace App\Http\Queue\Messages\Email;

class EmailVerificationMessage
{
    public function __construct(
        public string $email,
        public string $verifyToken
    ){}

}
