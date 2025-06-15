<?php

namespace App\Http\Models;

class Direction extends BaseModel
{
    const TABLE_NAME = 'directions';
    public function getAll(): array
    {
        $result = $this->database->fetchAllAssociative("SELECT id, name, slug FROM ". self::TABLE_NAME);
        return $result ?: [];
    }

    public function getBySlug(string $slug): array
    {
        $result = $this->database->fetchAssociative("SELECT id, name, slug FROM ".self::TABLE_NAME." WHERE slug = :slug", ['slug' => $slug]);
        return $result ?: [];
    }
}
