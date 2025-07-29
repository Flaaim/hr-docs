<?php

namespace App\Http\Services;

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class Cache
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger
    )
    {}

    public function cachedGet(string $key, callable $loader, ?int $ttl = null)
    {
        try {
            if ($this->cache->has($key)) {
                return $this->cache->get($key);
            }

            $data = $loader();
            $this->cache->set($key, $data, $ttl);
            return $data;
        } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
            $this->logger->error("Cache error for key '$key'", ['exception' => $e]);
            return $loader(); // Fallback без кеша
        }
    }
}
