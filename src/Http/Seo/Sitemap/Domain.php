<?php

namespace App\Http\Seo\Sitemap;

class Domain
{
    public static function url(): string
    {
        return $_ENV['APP_PATH'];
    }
    public static function priority(): float
    {
        return 1.0;
    }
    public static function changeFreq(): string
    {
        return 'daily';
    }
}
