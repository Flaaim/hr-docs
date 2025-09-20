<?php

namespace App\Http\Frontend;

class FrontendUrlGenerator
{
    private string $baseUrl;
    public function __construct(string $baseUrl){
        $this->baseUrl = $baseUrl;
    }

    public function generate(string $url, array $params = []): string
    {
        return $this->baseUrl .
            (!empty($url) ? '/' . ltrim($url, '/') : '') .
            (!empty($params) ? '?' . http_build_query($params) : '');

    }

}
