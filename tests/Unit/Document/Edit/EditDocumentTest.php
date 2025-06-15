<?php

namespace Document\Edit;

use App\Http\Documents\Document;
use App\Http\Documents\Edit\EditDocumentService;
use App\Http\Exception\SectionNotFoundException;
use App\Http\Exception\TypeNotFoundException;
use App\Http\Models\Section;
use App\Http\Models\Type;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EditDocumentTest extends TestCase
{
    private Section $mockSection;
    private Type $mockType;
    private Document $mockDocument;
    private EditDocumentService $service;

    public function setUp(): void
    {
        $this->mockSection = $this->createMock(Section::class);
        $this->mockType = $this->createMock(Type::class);
        $this->mockDocument = $this->createMock(Document::class);
        $this->service = new EditDocumentService($this->mockDocument, $this->mockSection, $this->mockType );
    }

    public function testEditDocument_Failed_dataEmpty()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->editDocument([]);
    }

    public function testEditDocument_Failed_SectionNotFound()
    {
        $this->mockSection->method('getById')->willReturn([]);
        $this->expectException(SectionNotFoundException::class);
        $this->service->editDocument(['title' => 'some_title', 'section_id' => 1, 'type_id' => 1]);
    }

    public function testEditDocument_Failed_TypeNotFound()
    {
        $this->mockSection->method('getById')->willReturn(['id' => 1, 'name' => 'some_name']);
        $this->mockType->method('getById')->willReturn([]);
        $this->expectException(TypeNotFoundException::class);
        $this->service->editDocument(['title' => 'some_title', 'section_id' => 1, 'type_id' => 1]);
    }

    public function testEditDocumentSuccess()
    {
        $this->mockSection->method('getById')->willReturn(['id' => 1, 'name' => 'some_name']);
        $this->mockType->method('getById')->willReturn(['id' => 1, 'name' => 'some_name']);
        $this->mockDocument->expects($this->once())->method('editById');
        $this->service->editDocument(['document_id' => 1, 'title' => 'some_title', 'section_id' => 1, 'type_id' => 1]);
    }
}
