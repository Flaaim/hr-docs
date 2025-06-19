<?php

namespace App\Http\Services;

class CookieManager
{
    public function get(string $name): ?string
    {
        return $_COOKIE[$name] ?? null;
    }
    public function set(
        string $name,
        string $value,
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = true,
        bool $httpOnly = false,
        string $sameSite = 'Lax'
    ): void {
        setcookie($name, $value, [
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite
        ]);
    }

    public function delete(string $name): void
    {
        $this->set($name, '', time() - 3600);
    }
}
