<?php

namespace App\Http\Documents\HandleFile;


class MimeTypeMapper
{
    private array $mimeTypesMap;
    public function __construct(array $mimeTypesMap)
    {
        $this->mimeTypesMap = $mimeTypesMap;
    }

    public function getExtensionFromMime(string $key): string
    {
        if(!isset($this->mimeTypesMap[$key])){
            throw new \InvalidArgumentException("Mime type $key does not exist");
        }
        return $this->mimeTypesMap[$key];
    }

    public function checkUploadedFiles(array $uploadedFiles): bool
    {
        foreach ($uploadedFiles as $file) {
            if(!array_key_exists($file->getClientMediaType(), $this->mimeTypesMap)){
                return false;
            }
        }
        return true;
    }
}
