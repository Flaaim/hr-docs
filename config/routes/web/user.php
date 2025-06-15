<?php

declare(strict_types=1);

/**
 * @var $app
 */

use App\Http\Auth\AuthMiddleware;
use App\Http\Controllers\UserController;
use Slim\Routing\RouteCollectorProxy;

$app->group('/user', function (RouteCollectorProxy $group) {
    $group->get('/dashboard', [UserController::class, 'dashboard']);
})->add($app->getContainer()->get(AuthMiddleware::class));
