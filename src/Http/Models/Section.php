<?php

namespace App\Http\Models;

class Section extends BaseModel
{
    const TABLE_NAME = 'sections';
    public function getAllByDirectionId(int $direction_id): array
    {
        $result = $this->database->fetchAllAssociative("SELECT id, name FROM ". self::TABLE_NAME. " WHERE direction_id = ?", [$direction_id]);
        return $result ?: [];
    }

    public function getById(int $id): array
    {
        $result = $this->database->fetchAssociative("SELECT id, direction_id, name FROM ". self::TABLE_NAME. " WHERE id = ?", [$id]);
        return $result ?: [];
    }
}
