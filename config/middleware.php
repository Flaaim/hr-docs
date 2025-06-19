<?php

declare(strict_types=1);

use App\Http\Auth\Auth;
use App\Http\Auth\AuthService;
use App\Http\Middleware\CsrfMiddleware;
use App\Http\Middleware\RememberMeMiddleware;
use App\Http\Services\CookieManager;
use Odan\Session\Middleware\SessionMiddleware;
use Odan\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Views\TwigMiddleware;

return static function (App $app, ContainerInterface $container): void {

    $app->addMiddleware(new SessionMiddleware($container->get(SessionInterface::class)));
    $app->addMiddleware(new RememberMeMiddleware(
        $container->get(AuthService::class),
        $container->get(Auth::class),
        $container->get(SessionInterface::class),
        $container->get(CookieManager::class)
        )
    );
    $app->addMiddleware(new CsrfMiddleware($container->get(SessionInterface::class)));
    $app->addMiddleware(TwigMiddleware::create($app, $container->get('Slim\Views\Twig')));

    $app->addErrorMiddleware($container->get('config')['debug'], true, true);
};
