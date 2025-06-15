<?php

namespace Document\Upload;

use App\Http\Documents\Document;
use App\Http\Documents\Upload\UploadDocumentService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;


class UploadDocumentServiceTest extends TestCase
{

    private Document $mockDocument;
    private UploadDocumentService $service;

    public function setUp(): void
    {
        $this->mockDocument = $this->createMock(Document::class);
        $this->service = new UploadDocumentService($this->mockDocument);

    }
    public function testInsertDocumentFailed()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->insertFiles([]);
    }

    public function testInsertDocumentSuccess()
    {
        $this->mockDocument->expects($this->once())->method('insertDocuments')->willReturn(1);
        $this->service->insertFiles(['some_array']);
    }
}
