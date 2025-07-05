<?php

namespace App\Http\Documents;

use App\Http\Documents\HandleFile\HandleFileData;
use App\Http\Models\BaseModel;
use Doctrine\DBAL\ArrayParameterType;

class Document extends BaseModel
{
    CONST TABLE_NAME = 'documents';
    public function insertDocuments(array $documents): int|string
    {
        /**
         * @param HandleFileData[] $documents Массив объектов DocumentData (даже с одним элементом)
         * @return int|string
         */
        $preparedData = array_map(
            fn(HandleFileData $doc) => $doc->jsonSerialize(),
            $documents
        );

        $columns = array_keys($preparedData[0]);
        $placeholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $valuePlaceholders = implode(', ', array_fill(0, count($preparedData), $placeholders));
        $sql = 'INSERT INTO ' . self::TABLE_NAME . ' (' . implode(', ', $columns) . ') VALUES ' . $valuePlaceholders;

        $updateParts = [];
        foreach ($columns as $column) {
            if ($column !== 'stored_name') {
                $updateParts[] = "$column = IF(VALUES($column) IS NOT NULL AND VALUES($column) != '', VALUES($column), $column)";
            }
        }
        $sql .= ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updateParts);

        $values = [];
        foreach ($preparedData as $row) {
            foreach ($columns as $column) {
                $values[] = $row[$column];
            }
        }
        return $this->database->executeStatement($sql, $values);
    }

    public function getAll(array $filters = [], int $limit = null, int $offset = null, array $in = []): array
    {
        $queryBuilder = $this->database->createQueryBuilder()
            ->select("d.*, s.name as section_name, s.id as section_id, t.name as type_name, t.id as type_id, dir.name as direction_name, dir.id as direction_id")
            ->from(self::TABLE_NAME, 'd')
            ->leftJoin('d', 'sections', 's', 's.id = d.section_id')
            ->leftJoin('s', 'directions', 'dir', 's.direction_id = dir.id')
            ->leftJoin('d', 'types', 't', 't.id = d.type_id')
        ->orderBy('updated', 'desc');

        foreach ($filters as $field => $value) {
            if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $field)) {
                continue;
            }
            $queryBuilder->andWhere("$field = :$field")
                ->setParameter($field, $value);
        }

        foreach ($in as $field => $values) {
            if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $field)) {
                continue;
            }
            if (!empty($values)) {
                $paramName = 'in_' . str_replace('.', '_', $field);
                $queryBuilder->andWhere($queryBuilder->expr()->in($field, ':' . $paramName))
                    ->setParameter($paramName, $values, ArrayParameterType::STRING);
            }
        }

        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        if ($offset) {
            $queryBuilder->setFirstResult($offset);
        }

        return $queryBuilder->fetchAllAssociative() ?: [];

    }
    public function getByDirection(int $direction_id, int $limit = null, int $offset = null): array
    {
        $result = $this->getAll(['direction_id' => $direction_id], $limit, $offset);
        return $result ?: [];
    }
    public function getBySection(int $section_id): array
    {
        $result = $this->getAll(['d.section_id' => $section_id]);
        return $result ?: [];
    }
    public function getByType(int $type_id): array
    {
        $result = $this->getAll(['d.type_id' => $type_id]);
        return $result ?: [];
    }
    public function getByInValues(string $field, array $values): array
    {
        if (empty($values)) {
            return [];
        }
        $result = $this->getAll([], null, null, [$field => $values]);
        return $result ?: [];
    }
    public function getCount(array $filters = []): int
    {
        $queryBuilder = $this->database->createQueryBuilder()
            ->select('COUNT(*)')->from(self::TABLE_NAME, 'd')
            ->leftJoin('d', 'sections', 's', 's.id = d.section_id')
            ->leftJoin('s', 'directions', 'dir', 's.direction_id = dir.id');

        foreach ($filters as $field => $value) {
            $queryBuilder->andWhere("$field = :$field")
                ->setParameter($field, $value);
        }

        return $queryBuilder->fetchOne() ?: 0;
    }

    public function getCountByDirection(int $direction_id): int
    {
        $result = $this->getCount(['direction_id' => $direction_id]);
        return $result ?: 0;
    }


    public function delete(int $document_id): int|string
    {
        $sql = "DELETE FROM " . self::TABLE_NAME . " WHERE id = ?";
        return $this->database->executeStatement($sql, [$document_id]);
    }

    public function getById(int $document_id): array
    {
        $result = $this->database->fetchAssociative("SELECT * FROM " . self::TABLE_NAME . " WHERE id = ?", [$document_id]);
        return $result ?: [];
    }
    public function getByStoredName(string $stored_name): array
    {
        $result = $this->database->fetchAssociative("SELECT * FROM " . self::TABLE_NAME . " WHERE stored_name = ?", [$stored_name]);
        return $result ?: [];
    }
    public function editById(int $id, string $title, int $section_id, int $type_id) : void
    {
        $this->database->update(self::TABLE_NAME,
            ['title' => $title,
            'section_id' => $section_id,
            'type_id' => $type_id,], ['id' => $id]
        );
    }

    public function getDocumentsFileNames(): array
    {
        $result = $this->database->fetchFirstColumn('SELECT stored_name FROM ' . self::TABLE_NAME);
        return $result ?: [];
    }
}
