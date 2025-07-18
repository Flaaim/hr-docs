<?php

namespace App\Http\Exception\Sitemap;

use Throwable;

class SitemapException extends \LogicException
{
    public function __construct($message = "Sitemap error", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
