<?php

declare(strict_types=1);

use Odan\Session\Middleware\SessionMiddleware;
use Odan\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Views\TwigMiddleware;

return static function (App $app, ContainerInterface $container): void {

    $app->addMiddleware(new SessionMiddleware($container->get(SessionInterface::class)));
    $app->addMiddleware(TwigMiddleware::create($app, $container->get('Slim\Views\Twig')));
    //
    $app->addErrorMiddleware($container->get('config')['debug'], true, true);
};
