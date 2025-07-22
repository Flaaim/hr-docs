<?php

namespace App\Http\Seo\Sitemap;

use App\Http\Exception\Sitemap\SitemapException;
use InvalidArgumentException;

class Sitemap
{
    private Documents $documents;
    private Pages $pages;
    public function __construct(Pages $pages, Documents $documents)
    {
        $this->pages = $pages;
        $this->documents = $documents;
    }
    public function generateDocumentIds(array $ids): void
    {
        if(empty($ids)) {
            throw new InvalidArgumentException('$ids must not be empty');
        }
        if(!array_filter($ids, 'is_int')) {
            throw new InvalidArgumentException('Document IDs must be integers');
        }
        $this->documents->setDocumentIds($ids)->generateLinks();
    }
    public function generate(): string
    {
        $path = dirname(__DIR__, 4) . '/public/sitemap.xml';

        $tempPath = $path . '.tmp';

        try {
            $sitemap = new \samdark\sitemap\Sitemap($tempPath);

            $sitemap->addItem(Domain::url(), time(), Domain::changeFreq(), Domain::priority());

            foreach ($this->pages->getLinks() as $page) {
                $sitemap->addItem($page, time(), Pages::changeFreq(), Pages::priority());
            }

            foreach ($this->documents->getLinks() as $document) {
                $sitemap->addItem($document, time(), Documents::changeFreq(), Documents::priority());
            }

            $sitemap->write();

            // Атомарная замена файла
            if (!rename($tempPath, $path)) {
                throw new SitemapException("Failed to replace sitemap file");
            }

            // Читаем конечный файл
            $xmlContent = file_get_contents($path);
            if (false === $xmlContent) {
                throw new SitemapException("Unable to read sitemap file");
            }

            return $xmlContent;
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }
}
