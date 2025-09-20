<?php

namespace App\Http\Frontend\Test;

use App\Http\Frontend\FrontendUrlGenerator;
use App\Http\Frontend\FrontendUrlTwigExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class FrontendUrlTwigExtensionTest extends TestCase
{
    public function testSuccess(): void
    {
        $frontendUrl = $this->createMock(FrontendUrlGenerator::class);
        $frontendUrl->expects($this->once())->method('generate')->with(
            $this->equalTo('path'),
            $this->equalTo([
                'a' => '1',
                'b' => '2',
            ]),
        )->willReturn($url = 'http://localhost/path?a=1&b=2');

        $twig = new Environment(new ArrayLoader([
            'page.html.twig' => '<p>{{ frontend_url(\'path\', {\'a\': 1, \'b\': 2}) }}</p>'
        ]));
        $twig->addExtension(new FrontendUrlTwigExtension($frontendUrl));
        $this->assertEquals('<p>http://localhost/path?a=1&amp;b=2</p>', $twig->render('page.html.twig'));
    }
}
