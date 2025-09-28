<?php

declare(strict_types=1);

use App\Http\Documents\HandleFile\MimeTypeMapper;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;

return [
    'config' => [
        'debug' => filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN),
        'env' => $_ENV['APP_ENV'],
        'path' => $_ENV['APP_PATH'],
        'mimeTypes' => [
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'
        ],
    ],
    ResponseFactoryInterface::class => Di\get(ResponseFactory::class),
    MimeTypeMapper::class => function (ContainerInterface $container) {
        return new MimeTypeMapper($container->get('config')['mimeTypes']);
    }
];
