<?php

namespace App\Http\Documents\Delete;

use App\Http\Documents\Document;
use App\Http\Documents\FileSystemService;
use App\Http\Exception\Document\DocumentNotFoundException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class DeleteDocumentService
{
    public function __construct(
        private readonly Document $document,
        private readonly FileSystemService $fileSystemService,
        private readonly LoggerInterface $logger
    )
    {}

    public function deleteDocument(array $document): int
    {
        if(empty($document)){
            throw new DocumentNotFoundException('Document not found', 404);
        }
        $rows = $this->document->delete($document['id']);
        if($rows === 0){
            throw new RuntimeException('Failed to delete document from database',500);
        }
        $file = $this->fileSystemService->generateUploadDir($document['stored_name'], $document['mime_type']);

        if(!$this->fileSystemService->unlink($file)){
            $this->logger->warning('Ошибка удаления файла', ['Файл:' => $file]);
            throw new RuntimeException('Document deleted from DB but not from filesystem', 500);
        }
        return $rows;
    }
    public function deleteDocumentFromDb(int $document_id): int
    {
        $rows = $this->document->delete($document_id);
        if($rows === 0){
            throw new RuntimeException('Failed to delete document from database',500);
        }
        return $rows;
    }
    public function deleteOrphanedDocument(string $filename): bool
    {
        $uploadDir = $this->fileSystemService->getUpoadDir();
        if(!is_dir($uploadDir)) {
            throw new DirectoryNotFoundException('Directory "' . $uploadDir . '" not found');
        }
        $file = $uploadDir . $filename;
        if(!$this->fileSystemService->fileExists($file)){
            throw new DocumentNotFoundException('File not found');
        }

        if (!is_writable($file)) {
            throw new RuntimeException("No write permissions for file: " . $file);
        }
        return $this->fileSystemService->unlink($file);
    }


}
