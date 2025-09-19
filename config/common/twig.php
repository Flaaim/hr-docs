<?php

use App\Http\Seo\SeoManager;
use App\Http\Twig\SeoExtension;
use Odan\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Twig\Environment;

use Twig\Loader\FilesystemLoader;


return [

    Twig::class => function (ContainerInterface $container) {
        $config = $container->get('config')['twig'];

        $loader = new FilesystemLoader();

        foreach($config['template_dirs'] as $alias => $dir) {
            $loader->addPath($dir, $alias);
        }

        $twig = new Twig($loader, [
            'cache' => $config['debug'] ? false : $config['cache_dir'],
            'debug' => $config['debug'],
            'strict_variables' => $config['debug'],
            'auto_reload' => $config['debug'],
        ]);
        // Добавление расширений (опционально)


        foreach ($config['extensions'] as $class) {
            $extension = $container->get($class);
            $twig->getEnvironment()->addExtension($extension);
        }

        // Добавление глобальных переменных (опционально)
        $twig->getEnvironment()->addGlobal('app', $container->get('config'));
        $twig->getEnvironment()->addGlobal('session', $container->get(SessionInterface::class));
        $twig->getEnvironment()->addGlobal('csrf_token', $container->get(SessionInterface::class)->get('csrf_token'));

        return $twig;
    },
    Environment::class => function (ContainerInterface $container) {
        $twig = $container->get(Twig::class);
        return $twig->getEnvironment();
    },
    'config' => [
        'twig' => [
            'debug' => filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN),
            'template_dirs' => [
                FilesystemLoader::MAIN_NAMESPACE => __DIR__ . '/../../templates',
            ],
            'cache_dir' => __DIR__ . '/../../var/cache/twig',
            'extensions' => [
                SeoExtension::class,
            ]
        ]
    ],
];
