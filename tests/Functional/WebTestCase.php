<?php

namespace Tests\Functional;

use App\Http\JsonResponse;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;

class WebTestCase extends TestCase
{
    protected static function html(string $method, string $uri): ServerRequestInterface
    {
        return self::request($method, $uri)
            ->withHeader('Accept', 'text/html')
            ->withHeader('Content-Type', 'text/html');
    }

    protected static function json(string $method, string $uri): ServerRequestInterface
    {
        return self::request($method, $uri)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/json');
    }

    protected static function request(string $method, string $path): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest($method, $path);
    }

    protected function app(): App
    {
        /** @var ContainerInterface $container */
        $container = require __DIR__ . '/../../config/container.php';

        /** @var App */
        return (require __DIR__ . '/../../config/app.php')($container);
    }
}
