<?php

namespace App\Http\Queue\Messages\Email;

class SendUpdateMessage
{
    public function __construct(
        public string $email,
        public string $text,
        public string $subject
    )
    {}
}
