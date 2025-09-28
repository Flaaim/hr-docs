<?php

declare(strict_types=1);

use App\Http\Middleware\CsrfMiddleware;
use Odan\Session\Middleware\SessionMiddleware;
use Odan\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use Slim\Views\TwigMiddleware;

return static function (App $app, ContainerInterface $container): void {

    $app->addMiddleware(new SessionMiddleware($container->get(SessionInterface::class)));
    $twig = $container->get('Slim\Views\Twig');

    $app->addMiddleware(new CsrfMiddleware($container->get(SessionInterface::class)));
    $app->addMiddleware(TwigMiddleware::create($app, $twig));

    $app->add(ErrorMiddleware::class);



};
