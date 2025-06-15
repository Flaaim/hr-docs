<?php

namespace Document;

use App\Http\Documents\Document;
use App\Http\Documents\DocumentService;
use App\Http\Exception\DirectionNotFoundException;
use App\Http\Models\Direction;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;


class DocumentServiceTest extends TestCase
{
    private DocumentService $documentService;
    private Direction $directionMock;
    private Document $documentMock;
    private array $direction = ['id' => 1, 'name' => 'Кадровый документооборот'];
    public function setUp(): void
    {
        $this->documentMock = $this->createMock(Document::class);
        $this->directionMock = $this->createMock(Direction::class);
        $this->documentService = new DocumentService($this->documentMock, $this->directionMock);
    }
    public function testGetDocumentsByDirectionSlugFailed_invalidSlug()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->documentService->getDocumentsByDirectionSlug('', 10);
    }

    public function testGetDocumentsByDirectionSlugFailed_emptyDirections()
    {
        $this->directionMock->method('getBySlug')->willReturn([]);
        $this->expectException(DirectionNotFoundException::class);
        $this->documentService->getDocumentsByDirectionSlug('test_slug', 10);
    }

    public function testGetDocumentsByDirectionSlugSuccess()
    {
        $this->directionMock->method('getBySlug')->willReturn($this->direction);
        $this->documentMock->expects($this->once())->method('getByDirection')->willReturn([]);
        $this->documentService->getDocumentsByDirectionSlug('test_slug', 10);
    }
}
