<?php

namespace App\Http\Documents\Delete;

use App\Http\Documents\Document;
use App\Http\Documents\FileSystemService;
use App\Http\Exception\DocumentNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteDocumentMiddleware implements MiddlewareInterface
{
    private Document $document;
    private FileSystemService $fileSystemService;
    public function __construct(Document $document, FileSystemService $fileSystemService)
    {
        $this->document = $document;
        $this->fileSystemService = $fileSystemService;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $document_id = (int)($request->getParsedBody()['document_id'] ?? 0);

        $document = $this->document->getById($document_id);

        if(empty($document)) {
            throw new DocumentNotFoundException('Document not found', 404);
        }
        $file = $this->fileSystemService->generateUploadDir($document['stored_name'], $document['mime_type']);
        if(!$this->fileSystemService->fileExists($file)){
            throw new DocumentNotFoundException('File not found', 404);
        }

        $request = $request->withAttribute('document_data', $document);
        return $handler->handle($request);
    }
}
