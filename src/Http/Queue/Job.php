<?php

namespace App\Http\Queue;

class Job
{
    public function __construct(
        public string $queue,
        public array $payload,
        public ?int $id = null
    ){}
}
