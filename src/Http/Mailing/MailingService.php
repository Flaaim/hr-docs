<?php

namespace App\Http\Mailing;

class MailingService
{
    public function __construct(private readonly Mailing $mailing)
    {}
    public function getAll(): array
    {
        return $this->mailing->getUsersMailingList();
    }
}
