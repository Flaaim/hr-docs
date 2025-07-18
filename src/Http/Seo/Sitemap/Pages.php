<?php

namespace App\Http\Seo\Sitemap;

class Pages
{
    private array $pages = [
        '/documents/hr-job-descriptions',
    ];
    private array $links = [];

    public function __construct(){
        $this->links = array_map(function($page) {
            return Domain::url().$page;
        }, $this->pages);
        return $this;
    }
    public static function priority(): float
    {
        return 0.8;
    }
    public static function changeFreq(): string
    {
        return 'daily';
    }
    public function getLinks(): array
    {
        return $this->links;
    }
}
