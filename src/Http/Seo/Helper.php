<?php

namespace App\Http\Seo;

class Helper
{
    public static function createKeywordsFromTitle(string $title): string
    {
        $normalized = mb_strtolower(trim($title));

        $normalized = preg_replace('/[^\p{L}\p{N}\s]/u', '', $normalized);

        $words = preg_split('/\s+/', $normalized);

        $keywords = array_filter($words, function($word) {
            return strlen($word) > 2;
        });
        return implode(', ', array_values(array_unique($keywords)));
    }
}
