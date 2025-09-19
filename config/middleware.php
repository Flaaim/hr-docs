<?php

declare(strict_types=1);

use App\Http\Handlers\HttpForbiddenHandler;
use App\Http\Handlers\HttpInternalErrorHandler;
use App\Http\Handlers\HttpMethodNotAllowedHandler;
use App\Http\Handlers\HttpNotFoundHandler;
use App\Http\Middleware\CsrfMiddleware;
use Odan\Session\Middleware\SessionMiddleware;
use Odan\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\TwigMiddleware;

return static function (App $app, ContainerInterface $container): void {

    $app->addMiddleware(new SessionMiddleware($container->get(SessionInterface::class)));
    $twig = $container->get('Slim\Views\Twig');

    $app->addMiddleware(new CsrfMiddleware($container->get(SessionInterface::class)));
    $app->addMiddleware(TwigMiddleware::create($app, $twig));

    $errorMiddleware = $app->addErrorMiddleware($container->get('config')['debug'], true, true);

    $errorMiddleware->setErrorHandler(
        HttpNotFoundException::class,
        HttpNotFoundHandler::class
    );

    $errorMiddleware->setErrorHandler(
        HttpForbiddenException::class,
        HttpForbiddenHandler::class
    );

    $errorMiddleware->setErrorHandler(
        HttpInternalServerErrorException::class,
        HttpInternalErrorHandler::class
    );

    $errorMiddleware->setErrorHandler(
        HttpMethodNotAllowedException::class,
        HttpMethodNotAllowedHandler::class
    );

};
