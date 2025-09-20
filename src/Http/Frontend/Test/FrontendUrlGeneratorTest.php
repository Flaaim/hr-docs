<?php

namespace App\Http\Frontend\Test;

use App\Http\Frontend\FrontendUrlGenerator;
use PHPUnit\Framework\TestCase;


class FrontendUrlGeneratorTest extends TestCase
{
    public function testSuccess(): void
    {
        $urlGenerator = new FrontendUrlGenerator('http://localhost');

        $url = $urlGenerator->generate('some-url', ['token' => 'some-token']);

        $this->assertSame('http://localhost/some-url?token=some-token', $url);
    }

    public function testEmptyUrl(): void
    {
        $urlGenerator = new FrontendUrlGenerator($baseUrl = 'http://localhost');
        $url = $urlGenerator->generate('');
        $this->assertEquals($baseUrl, $url);
    }

    public function testTrimLeftSlashUrl(): void
    {
        $urlGenerator = new FrontendUrlGenerator($baseUrl = 'http://localhost');
        $url = $urlGenerator->generate('/some-url');

        $this->assertSame('http://localhost/some-url', $url);
    }

    public function testEmptyUrlWithParams(): void
    {
        $urlGenerator = new FrontendUrlGenerator($baseUrl = 'http://localhost');
        $url = $urlGenerator->generate('', ['token' => 'some-token']);

        $this->assertEquals('http://localhost?token=some-token', $url);
    }
}
