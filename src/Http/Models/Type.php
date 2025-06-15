<?php

namespace App\Http\Models;

class Type extends BaseModel
{
    const TABLE_NAME = 'types';
    public function getAll(): array
    {
        $result = $this->database->fetchAllAssociative("SELECT id, name FROM ".self::TABLE_NAME);
        return $result ?: [];
    }

    public function getById(int $id): array
    {
        $result = $this->database->fetchAssociative("SELECT id, name FROM ". self::TABLE_NAME. " WHERE id = ?", [$id]);
        return $result ?: [];
    }
}
