<?php

namespace Document\Delete;

use App\Http\Documents\Delete\DeleteDocumentMiddleware;
use App\Http\Documents\Document;
use App\Http\Documents\FileSystemService;
use App\Http\Exception\Document\DocumentNotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteDocumentMiddlewareTest extends TestCase
{
    private DeleteDocumentMiddleware $middleware;
    private Document $mockDocument;
    private array $document = [
        'id' => 1,
        'stored_name' => 'filename',
        'mime_type' => 'docx'
    ];
    public function setUp(): void
    {
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->mockDocument = $this->createMock(Document::class);
        $this->mockFileSystem = $this->createMock(FileSystemService::class);
        $this->middleware = new DeleteDocumentMiddleware($this->mockDocument, $this->mockFileSystem);
    }

    public function testDeleteDocumentFailed_NotFoundInDb()
    {
        $this->mockDocument->method('getById')->willReturn([]);
        $this->expectException(DocumentNotFoundException::class);
        $this->middleware->process($this->request, $this->handler);
    }

    public function testDeleteDocumentFailed_NotFoundInFileSystem()
    {
        $this->mockDocument->method('getById')->willReturn($this->document);
        $this->mockFileSystem->method('generateUploadDir')->willReturn('filename.docx');
        $this->mockFileSystem->method('fileExists')->willReturn(false);
        $this->expectException(DocumentNotFoundException::class);
        $this->middleware->process($this->request, $this->handler);
    }

    public function testDeleteDocumentSuccess()
    {
        $this->mockDocument->method('getById')->willReturn($this->document);
        $this->mockFileSystem->method('generateUploadDir')->willReturn('filename.docx');
        $this->mockFileSystem->method('fileExists')->willReturn(true);
        $this->request->method('withAttribute')->willReturn($this->request);
        $this->handler->expects($this->once())->method('handle')->with($this->request);
        $this->middleware->process($this->request, $this->handler);
    }

}
