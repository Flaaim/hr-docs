<?php

namespace Document;

use App\Http\Documents\FileSystemService;
use PHPUnit\Framework\TestCase;


class FileSystemServiceTest extends TestCase
{
    private FileSystemService $fileSystemService;
    public function setUp(): void
    {
        $this->fileSystemService = new FileSystemService();
    }
    public function testGenerateUploadDirFailed()
    {
        $uploadDir = $this->fileSystemService->generateUploadDir('filename', 'docx');
        $array = explode('/', $uploadDir);
        $this->assertEquals('filename.docx', end($array));
    }
}
