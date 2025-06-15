<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Flash\Messages;
use Slim\Psr7\Factory\ResponseFactory;

return [
    'config' => [
        'debug' => (bool)getenv('APP_DEBUG'),
        'path' => $_ENV['APP_PATH'],
    ],
    ResponseFactoryInterface::class => Di\get(ResponseFactory::class),
];
