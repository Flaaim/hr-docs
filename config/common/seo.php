<?php

declare(strict_types=1);

use App\Http\Seo\SeoManager;
use Psr\Container\ContainerInterface;
use Spatie\SchemaOrg\Schema;

return [
    SeoManager::class => function(ContainerInterface $container)  {
        return new SeoManager([
            'logo' => $_ENV['SEO_LOGO'],
            'title' => $_ENV['SEO_TITLE'],
            'description' => $_ENV['SEO_DESCRIPTION'],
            'keywords' => $_ENV['SEO_KEYWORDS'],
        ]);
    }
];
