<?php

namespace App\Http\Exception\Sitemap;

use Throwable;

class SitemapNotFoundException extends \LogicException
{
    public function __construct($message = "Sitemap not found", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
