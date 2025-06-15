<?php

namespace Document\Delete;

use App\Http\Documents\Delete\DeleteDocumentService;
use App\Http\Documents\Document;
use App\Http\Documents\FileSystemService;
use App\Http\Exception\DocumentNotFoundException;
use PHPUnit\Event\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class DeleteDocumentServiceTest extends TestCase
{
    private DeleteDocumentService $service;
    private Document $mockDocument;
    private array $document = [
        'id' => 1,
        'stored_name' => 'filename',
        'mime_type' => 'docx'
    ];
    public function setUp(): void
    {
        $this->mockDocument = $this->createMock(Document::class);
        $this->mockFileSystem = $this->createMock(FileSystemService::class);
        $this->service = new DeleteDocumentService($this->mockDocument, $this->mockFileSystem);
    }
    public function testDeleteDocumentFailed_document_not_found()
    {
        $this->expectException(DocumentNotFoundException::class);
        $this->service->deleteDocument([]);
    }

    public function testDeleteDocumentFailed_notFoundDocument_inDb()
    {
        $this->mockDocument->method('delete')->willReturn(1);
        $this->expectException(RuntimeException::class);
        $this->service->deleteDocument($this->document);
    }

    public function testDeleteDocumentFailed_notFoundDocument_inFileSystem()
    {
        $this->mockDocument->method('delete')->willReturn(1);
        $this->mockFileSystem->method('generateUploadDir')->willReturn('filename.docx');
        $this->mockFileSystem->method('unlink')->willReturn(false);
        $this->expectException(RuntimeException::class);
        $this->service->deleteDocument($this->document);
    }

}
