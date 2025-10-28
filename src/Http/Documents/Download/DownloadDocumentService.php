<?php

namespace App\Http\Documents\Download;

use App\Http\Documents\Document;
use App\Http\Documents\FileSystemService;
use App\Http\Exception\Document\DocumentNotFoundException;
use App\Http\Exception\Document\DownloadLimitExceededException;
use App\Http\Exception\Document\FileNotFoundInStorageException;
use App\Http\Subscription\Subscription;
use App\Http\Subscription\SubscriptionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Stream;


class DownloadDocumentService
{
    private Subscription $subscription;
    private Document $document;
    private FileSystemService $fileSystemService;
    private LoggerInterface $logger;
    private SubscriptionService $subscriptionService;
    public function __construct(
        Subscription $subscription,
        Document $document,
        FileSystemService $fileSystemService,
        LoggerInterface $logger,
        SubscriptionService $subscriptionService
    )
    {
        $this->subscription = $subscription;
        $this->document = $document;
        $this->fileSystemService = $fileSystemService;
        $this->logger = $logger;
        $this->subscriptionService = $subscriptionService;
    }

    public function getDocument(int $document_id): array
    {
        $document = $this->document->getById($document_id);
        if(empty($document)){
            throw new DocumentNotFoundException('Документ не найден');
        }
        $this->validateFile($document['stored_name'], $document['mime_type']);
        return $document;
    }

    public function performFileDownload(array $document): ResponseInterface
    {
        $filePath = $this->fileSystemService->generateUploadDir(
            $document['stored_name'],
            $document['mime_type']
        );
        if(!$this->fileSystemService->fileExists($filePath)){
            $this->logger->warning('Файл не найден в хранилище', [
                'path' => $filePath,
            ]);
            throw new FileNotFoundInStorageException('Файл в хранилище не найден');
        }
        while (ob_get_level()) ob_end_clean();
        $fileStream = fopen($filePath, 'rb');
        $filename = $document['stored_name']. '.'.$document['mime_type'];

        register_shutdown_function(function() use ($fileStream) {
            if (is_resource($fileStream)) fclose($fileStream);
        });

        $response = (new ResponseFactory())->createResponse();
        return $response
            ->withHeader('Content-Type', $document['mime_type'])
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Content-Length', filesize($filePath))
            ->withBody(new Stream($fileStream));
    }

    public function validateFile(string $stored_name, string $mime_type): void
    {
        $filePath = $this->fileSystemService->generateUploadDir(
            $stored_name,
            $mime_type
        );
        if(!$this->fileSystemService->fileExists($filePath)){
            $this->logger->warning('Файл не найден в хранилище', [
                'path' => $filePath,
            ]);
            throw new FileNotFoundInStorageException('Файл не найден в хранилище');
        }
    }

    public function checkDownloadLimit(int $user_id): void
    {
        $current_plan = $this->subscription->getCurrentPlan($user_id);

        if($this->hasDownloadLimitExceeded($current_plan)){
            $this->subscriptionService->downgradeToFreePlan($user_id);
            throw new DownloadLimitExceededException('Лимит скачиваний исчерпан');
        }

        if($current_plan['downloads_remaining'] !== null){
            $this->subscription->decrementDownloads(
                $user_id, $current_plan['downloads_remaining'] - 1
            );
        }
    }

    private function hasDownloadLimitExceeded(array $current_plan): bool
    {
        return $current_plan['downloads_remaining'] !== Subscription::UNLIMITED_DOWNLOADS
            && $current_plan['downloads_remaining'] <= 0;
    }
}
