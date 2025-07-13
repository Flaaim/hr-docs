<?php

namespace App\Http\Services\Mail\EmailVerification;

class EmailVerificationMessage
{
    public function __construct(
        public readonly string $email,
        public readonly string $verifyToken
    ){}

}
