<?php

namespace App\Http\Queue;

use App\Http\Models\BaseModel;

class Queue extends BaseModel
{
    private const TABLE_NAME = 'queue_jobs';
    public function push(string $queue, array $data): void
    {
        $this->database->insert(self::TABLE_NAME, [
            'queue' => $queue,
            'job_data' => json_encode($data),
        ]);
    }

    public function pop(string $queue): ?Job
    {
        $this->database->beginTransaction();

        try{
            $job = $this->database->fetchAssociative(
                "SELECT * FROM " . self::TABLE_NAME . "
                        WHERE queue = ? AND processed_at IS NULL
                        AND (is_failed = FALSE)
                        ORDER BY created_at ASC
                        FOR UPDATE SKIP LOCKED",
                [$queue]
            );

            if (!$job) {
                $this->database->commit();
                return null;
            }
            $this->database->update(self::TABLE_NAME, ['processed_at' => date('Y-m-d H:i:s')], ['id' => $job['id']]);
            $this->database->commit();
            return new Job(
                $job['queue'],
                json_decode($job['job_data'], true),
                $job['id']
            );
        }catch (\Exception $e){
            $this->database->rollback();
            throw $e;
        }
    }

    public function markFailed(int $jobId): void
    {
        $this->database->executeQuery(
            "UPDATE " . self::TABLE_NAME . " SET is_failed = TRUE WHERE id = ? FOR UPDATE",
            [$jobId]
        );
    }
}
