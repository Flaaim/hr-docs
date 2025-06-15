<?php

use Odan\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Slim\Views\Twig;

return [
    Twig::class => function (ContainerInterface $container) {
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $twig = new Twig($loader, [
            'cache' => getenv('APP_ENV') === 'prod'
                ? __DIR__ . '/../var/cache/twig'
                : false,
            'debug' => getenv('APP_ENV') !== 'prod',
            'auto_reload' => getenv('APP_ENV') !== 'prod',
        ]);
        // Добавление расширений (опционально)
        if (getenv('APP_ENV') !== 'prod') {
            $twig->addExtension(new \Twig\Extension\DebugExtension());
        }
        // Добавление глобальных переменных (опционально)
        $twig->getEnvironment()->addGlobal('app', $container->get('config'));
        $twig->getEnvironment()->addGlobal('session', $container->get(SessionInterface::class));
        //$twig->getEnvironment()->addGlobal('flash', $container->get(Messages::class));
        return $twig;
    },
    Environment::class => function () {
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        return new Environment($loader);
    }
];
