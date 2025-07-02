<?php

declare(strict_types=1);

use Aego\OAuth2\Client\Provider\Yandex;
use League\OAuth2\Client\Provider\Google;
use Psr\Container\ContainerInterface;

return [
    'oauth.config' => [
        'yandex' => [
            'clientId'     => $_ENV['YANDEX_CLIENT_ID'],
            'clientSecret' => $_ENV['YANDEX_CLIENT_SECRET'],
            'redirectUri'  => $_ENV['YANDEX_REDIRECT_URL'],
        ],
        'google' => [
            'clientId'     => $_ENV['GOOGLE_CLIENT_ID'],
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'],
            'redirectUri'  => $_ENV['GOOGLE_REDIRECT_URL'],
        ],
    ],
    Yandex::class => function (ContainerInterface $container) {
        $config = $container->get('oauth.config')['yandex'];
        return new Yandex($config);
    },
    Google::class => function (ContainerInterface $container) {
        $config = $container->get('oauth.config')['google'];
        return new Google($config);
    }

];
