<?php

declare(strict_types=1);

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


return [
    CacheInterface::class => function () {
        $cacheDir = dirname(__DIR__, 2). '/var/cache';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $adapter = new FilesystemAdapter('documents_cache', 3600, $cacheDir);
        return new Psr16Cache($adapter);
    }
];
