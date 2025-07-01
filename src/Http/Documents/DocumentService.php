<?php

namespace App\Http\Documents;

use App\Http\Exception\Document\DirectionNotFoundException;
use App\Http\Exception\Document\DocumentNotFoundException;
use App\Http\Models\Direction;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class DocumentService
{
    public function __construct(
        private readonly Document $document,
        private readonly Direction $direction,
        private readonly FileSystemService $fileSystemService
    )
    {}

    public function getDirectionBySlug(string $slug): array
    {
        if(empty(trim($slug))) {
            throw new InvalidArgumentException('Direction slug cannot be empty');
        }
        return $this->direction->getBySlug($slug);
    }
    public function getDocumentsByDirectionSlug(string $slug, int $offset): array
    {
        $direction = $this->getDirectionBySlug($slug);
        if(empty($direction)){
            throw new DirectionNotFoundException('Directions not found');
        }
        $documents = $this->document->getByDirection($direction['id'], $offset);
        return [
            'documents' => $documents,
            'direction' => $direction,
        ];
    }

    public function getCountByDirectionSlug(string $slug): int
    {
        $direction = $this->getDirectionBySlug($slug);
        if(empty($direction)){
            throw new DirectionNotFoundException('Directions not found');
        }
        return $this->document->getCountByDirection($direction['id']);
    }

    public function findOrphanedFiles(): array
    {
        $documentsFileNames = $this->document->getDocumentsFileNames();
        if (empty($documentsFileNames)) {
            throw new DocumentNotFoundException('Documents filenames not found');
        }
        $uploadDir = $this->fileSystemService->getUpoadDir();
        if(!is_dir($uploadDir)) {
            throw new DirectoryNotFoundException('Directory "' . $uploadDir . '" not found');
        }

        $filesInDirectory = scandir($uploadDir);
        if($filesInDirectory === false) {
            throw new RuntimeException('Error reading directory "' . $uploadDir . '"');
        }
        $fsFiles = array_diff($filesInDirectory, ['.', '..']);
        $fileMap = [];
        foreach ($fsFiles as $fullName) {
            $nameWithoutExt = pathinfo($fullName, PATHINFO_FILENAME);
            $fileMap[$nameWithoutExt] = $fullName;
        }

        $orphanedNamesWithoutExt = array_diff(array_keys($fileMap), $documentsFileNames);

        $orphanedFiles = [];
        foreach ($orphanedNamesWithoutExt as $name) {
            if (isset($fileMap[$name])) {
                $orphanedFiles[] = $fileMap[$name];
            }
        }
        return $orphanedFiles;
    }
}
