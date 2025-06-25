<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use YooKassa\Client;

return [
    'payment' => [
        'yookassa' => [
            'shop_id' => $_ENV['YOOKASSA_SHOP_ID'],
            'secret_key' => $_ENV['YOOKASSA_SECRET_KEY'],
        ]
    ],
    Client::class => function (ContainerInterface $container) {
        $config = $container->get('payment')['yookassa'];
        $client = new Client();
        $client->setAuth($config['shop_id'], $config['secret_key']);
        return $client;
    }
];
