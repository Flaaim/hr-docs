<?php

namespace App\Http\Services;

class Seo
{
    public static function createKeywordsFromTitle(string $title): array
    {
        $words = preg_split('/\s+/', trim(mb_strtolower($title)));
        $keywords = array_filter($words, function($word) {
            $clean = preg_replace('/[^\p{L}\p{N}]/u', '', $word);
            return strlen($word) > 2;
        });
        return array_values(array_unique($keywords));
    }
}
