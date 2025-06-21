<?php

namespace App\Http\Documents\Edit;

use App\Http\Documents\Document;
use App\Http\Exception\Document\SectionNotFoundException;
use App\Http\Exception\Document\TypeNotFoundException;
use App\Http\Models\Section;
use App\Http\Models\Type;
use InvalidArgumentException;

class EditDocumentService
{
    private Document $document;
    private Section $section;
    private Type $type;
    public function __construct(Document $document, Section $section, Type $type)
    {
        $this->document = $document;
        $this->section = $section;
        $this->type = $type;
    }

    public function editDocument(array $data): void
    {
        if(empty($data)){
            throw new InvalidArgumentException('Data to edit document is empty');
        }
        $section = $this->section->getById($data['section_id']);
        if(empty($section)){
            throw new SectionNotFoundException('Section not found');
        }
        $type = $this->type->getById($data['type_id']);
        if(empty($type)){
            throw new TypeNotFoundException('Type not found');
        }
        $this->document->editById($data['document_id'], $data['title'], $data['section_id'], $data['type_id']);
    }

}
