<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;

return [
    'config' => [
        'debug' => true, //filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN),
        'path' => $_ENV['APP_PATH'],
    ],
    ResponseFactoryInterface::class => Di\get(ResponseFactory::class),
];
