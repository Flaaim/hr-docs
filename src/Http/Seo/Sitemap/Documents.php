<?php

namespace App\Http\Seo\Sitemap;

use App\Http\Documents\Document;

class Documents
{
    private array $ids = [];
    private array $links = [];
    public function setDocumentIds(array $documentIds): self
    {
        $this->ids = $documentIds;
        return $this;
    }

    public static function priority(): float
    {
        return 0.6;
    }
    public static function changeFreq(): string
    {
        return 'daily';
    }
    public function generateLinks(): self
    {
        $this->links = array_map(function ($documentId) {
            return Domain::url() . '/document/' . $documentId;
        }, $this->ids);
        return $this;
    }
    public function getLinks(): array
    {
        return $this->links;
    }
}
