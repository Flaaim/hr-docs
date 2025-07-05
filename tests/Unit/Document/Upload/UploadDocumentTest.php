<?php

namespace Document\Upload;

use App\Http\Documents\FileSystemService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class UploadDocumentTest extends TestCase
{
    private UploadedFileInterface $mockFile;
    private ServerRequestInterface $request;
    private ResponseInterface $response;
    private RequestHandlerInterface $handler;
    private FileNameHelper $filename;

    public function setUp(): void
    {
        $this->mockFile = $this->createMock(UploadedFileInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->filename = $this->createMock(FileNameHelper::class);
        $this->fileSystemMock = $this->createMock(FileSystemService::class);

    }
    public function testUploadDocumentFailed_FileNotUploaded()
    {
        $this->request->method('getUploadedFiles')->willReturn(null);
        $middleware = new UploadDocumentMiddleware($this->filename, $this->fileSystemMock);
        $result = $middleware->process($this->request, $this->handler);
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testUploadDocumentFailed_FileToBig()
    {
        $this->mockFile->method('getError')->willReturn(UPLOAD_ERR_FORM_SIZE);
        $this->request->method('getUploadedFiles')->willReturn(['file' => [$this->mockFile]]);

        $middleware = new UploadDocumentMiddleware($this->filename, $this->fileSystemMock);
        $result = $middleware->process($this->request, $this->handler);
        $this->assertEquals(413, $result->getStatusCode());
    }

    public function testUploadDocumentFailedToWriteFile()
    {
        $this->mockFile->method('getError')->willReturn(UPLOAD_ERR_OK);
        $this->mockFile->method('getClientFilename')->willReturn('some_filename.txt');
        $this->mockFile->method('moveTo')->willThrowException(new RuntimeException());
        $this->request->method('getUploadedFiles')->willReturn(['file' => [$this->mockFile]]);

        $middleware = new UploadDocumentMiddleware($this->filename, $this->fileSystemMock);
        $result = $middleware->process($this->request, $this->handler);
        $this->assertEquals(500, $result->getStatusCode());
    }

    public function testUploadFailed_WrongMime()
    {
        $this->mockFile->method('getError')->willReturn(UPLOAD_ERR_PARTIAL);
        $this->request->method('getUploadedFiles')->willReturn(['file' => [$this->mockFile]]);

        $middleware = new UploadDocumentMiddleware($this->filename, $this->fileSystemMock);
        $result = $middleware->process($this->request, $this->handler);
        $this->assertEquals(400, $result->getStatusCode());
    }

    public function testUploadSuccess()
    {
        $this->mockFile->method('getError')->willReturn(UPLOAD_ERR_OK);
        $this->request->method('getUploadedFiles')->willReturn(['file' => $this->mockFile]);
        $this->mockFile->method('getClientFilename')->willReturn('some_filename.txt');
        $this->mockFile->method('moveTo')->willReturn(1);
        $this->request->method('withAttribute')->willReturn($this->request);
        $this->handler->expects($this->once())->method('handle')->with($this->request);

        $middleware = new UploadDocumentMiddleware($this->filename, $this->fileSystemMock);
        $middleware->process($this->request, $this->handler);
    }


}
