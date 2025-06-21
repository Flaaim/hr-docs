<?php

namespace App\Http\Documents;

use App\Http\Exception\Document\DirectionNotFoundException;
use App\Http\Models\Direction;
use InvalidArgumentException;

class DocumentService
{
    private Document $document;
    private Direction $direction;

    public function __construct(Document $document, Direction $direction)
    {
        $this->document = $document;
        $this->direction = $direction;
    }
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
}
