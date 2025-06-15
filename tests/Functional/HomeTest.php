<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Request;

class HomeTest extends WebTestCase
{
    public function testSuccess()
    {
        $this->markTestSkipped('Временно отключен для рефакторинга');
        $response = $this->app()->handle(self::html('GET', '/'));

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('text/html', $response->getHeaderLine('Content-Type'));
    }

}
