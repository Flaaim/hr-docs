<?php

namespace Document\Download;

use App\Http\Documents\Document;
use App\Http\Documents\Download\DownloadDocumentService;
use App\Http\Documents\FileSystemService;
use App\Http\Exception\Document\DocumentNotFoundException;
use App\Http\Exception\Document\FileNotFoundInStorageException;
use App\Http\Subscription\Subscription;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class DownloadDocumentTest extends TestCase
{
    private ServerRequestInterface $mockRequest;
    private SessionInterface $mockSession;
    private FileSystemService $mockFileSystemService;
    private Document $mockDocument;
    private Subscription $mockSubscribe;
    private DownloadDocumentService $service;
    private LoggerInterface $mockLogger;
    public function setUp(): void
    {
        $this->mockSession = $this->createMock(SessionInterface::class);
        $this->mockFileSystemService = $this->createMock(FileSystemService::class);
        $this->mockDocument = $this->createMock(Document::class);
        $this->mockSubscribe = $this->createMock(Subscription::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);

        $this->service = new DownloadDocumentService(
            $this->mockSubscribe,
            $this->mockDocument,
            $this->mockFileSystemService,
            $this->mockLogger,
        );
    }

    public function testGetDocument_NotFound()
    {
        $this->mockDocument->method('getById')->willReturn([]);
        $this->expectException(DocumentNotFoundException::class);
        $this->service->getDocument(15, 1);

    }

    public function testGetDocument_Success()
    {
        $this->mockDocument->method('getById')->willReturn(['stored_name' => 'some_name', 'mime_type' => 'docx']);
        $mock = $this->getMockBuilder(DownloadDocumentService::class)->setConstructorArgs([
            $this->mockSubscribe,
            $this->mockDocument,
            $this->mockFileSystemService,
            $this->mockLogger
        ])->onlyMethods(['validateFile'])
            ->getMock();
        $this->mockFileSystemService->method('fileExists')->willReturn(true);
        $this->mockFileSystemService->method('generateUploadDir')->willReturn('upload_file_string');
        $mock->expects($this->once())->method('validateFile');
        $mock->getDocument(15, 1);
    }

    public function testValidateFile_FailedFileNotExists()
    {
        $this->mockFileSystemService->method('generateUploadDir')->willReturn('some_string_path');
        $this->mockFileSystemService->method('fileExists')->willReturn(false);
        $this->expectException(FileNotFoundInStorageException::class);
        $this->service->validateFile('stored_name', 'mime_type');

    }








}
