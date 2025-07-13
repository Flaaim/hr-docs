<?php

namespace App\Http\Queue;

use App\Http\Services\Mail\Mail;
use Psr\Log\LoggerInterface;

class Worker
{
    public function __construct(
        private readonly Queue $queue,
        private readonly LoggerInterface $logger,
        private readonly Mail $mailer
    )
    {}
    public function work(string $queueName, int $timeout = 60): void
    {
        $startTime = time();
        while (time() - $startTime < $timeout){
            $job = $this->queue->pop($queueName);

            if (!$job) {
                sleep(1);
                continue;
            }

            try{
                $this->processJob($job);
            }catch (\Throwable $e){
                if($job->id){
                    $this->queue->markFailed($job->id);
                }
                $this->logger->error('Job failed:', [
                    'job' => $job->queue,
                    'jobId' => $job->id,
                    'exception' => $e->getMessage(),
                ]);
            }

        }
    }

    private function processJob(Job $job): void
    {
        switch ($job->queue) {
            case 'sendVerificationEmail':
                $this->mailer->sendVerificationEmail($job->payload);
            break;
        }
    }
}
