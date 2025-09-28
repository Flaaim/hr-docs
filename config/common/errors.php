<?php

declare(strict_types=1);

use App\Http\ErrorHandler\LogErrorHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\CallableResolver;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Views\Twig;

return [
    ErrorMiddleware::class => function (ContainerInterface $container) {
        /** @var CallableResolverInterface $callableResolver */
        $callableResolver = $container->get(CallableResolver::class);
        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $container->get(ResponseFactory::class);

        $twig = $container->get(Twig::class);

        $config = $container->get('config')['errors'];

        $middleware =  new ErrorMiddleware(
            $callableResolver,
            $responseFactory,
            $config['display_details'],
            true,
            true
        );

        /** @var LoggerInterface $logger */
        $logger = $container->get(LoggerInterface::class);

        $middleware->setDefaultErrorHandler(
            new LogErrorHandler($callableResolver, $responseFactory, $logger, $twig)
        );
        return $middleware;
    },
    'config' => [
        'errors' => [
            'display_details' => (bool)getenv('APP_DEBUG'),
        ],
    ],
];
