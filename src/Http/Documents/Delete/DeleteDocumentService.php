<?php

namespace App\Http\Documents\Delete;

use App\Http\Documents\Document;
use App\Http\Documents\FileSystemService;
use App\Http\Exception\Document\DocumentNotFoundException;
use RuntimeException;

class DeleteDocumentService
{
    private Document $document;
    private FileSystemService $fileSystemService;

    public function __construct(Document $document, FileSystemService $fileSystemService)
    {

        $this->document = $document;
        $this->fileSystemService = $fileSystemService;
    }

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
           //Логируем ошибку удаления файла.
            throw new RuntimeException('Document deleted from DB but not from filesystem', 500);
        }
        return $rows;
    }


}
