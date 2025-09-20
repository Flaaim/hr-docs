<?php

namespace App\Http\Queue\Messages\Email;

class EmailPaymentMessage
{
    public function __construct(
        public readonly string $email,
        public readonly string $subject,
        public readonly string $amount,
        public readonly string $slug,
    )
    {}
}
