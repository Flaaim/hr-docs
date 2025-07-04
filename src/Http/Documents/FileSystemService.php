<?php

namespace App\Http\Documents;

class FileSystemService
{

    public function generateUploadDir(string $filename, string $extension): string
    {
        return $this->getUpoadDir() . $filename.'.'.$extension;
    }
    public function getUpoadDir(): string
    {
        return dirname(__DIR__, 3).'/public/uploads/';
    }
    public function fileExists($filePath): bool
    {
        return file_exists($filePath);
    }

    public function unlink($filePath): bool
    {
        return unlink($filePath);
    }
}
