<?php

namespace App\Http\Documents\HandleFile;

class HandleFileData implements \JsonSerializable
{
    private string $title = '';
    private string $stored_name = '';
    private string $mime_type = '';
    private int $section_id = 0;
    private int $type_id = 0;
    private int $size = 0;
    private int $updated;

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setStoredName(string $stored_name): self
    {
        $this->stored_name = $stored_name;
        return $this;
    }

    public function setMimeType(string $mime_type): self
    {
        $this->mime_type = $mime_type;
        return $this;
    }
    public function setSectionId(int $section_id): self
    {
        $this->section_id = $section_id;
        return $this;
    }

    public function setTypeId(int $type_id): self
    {
        $this->type_id = $type_id;
        return $this;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function setUpdated(int $updated = null): self
    {
        $this->updated = ($updated) ?: (new \DateTimeImmutable())->getTimestamp();
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'title' => $this->title,
            'stored_name' => $this->stored_name,
            'mime_type' => $this->mime_type,
            'section_id' => $this->section_id,
            'type_id' => $this->type_id,
            'size' => $this->size,
            'updated' => $this->updated
        ];
    }
}
