<?php

declare(strict_types=1);


/**
 * @var $app
 */

use App\Http\Middleware\AdminMiddleware;
use App\Http\Users\UserController;
use Slim\Routing\RouteCollectorProxy;

$app->group('/api/users', function (RouteCollectorProxy $group) {
    $group->get('/all', [UserController::class, 'all']);

    $group->get('/get', [UserController::class, 'get']);

    $group->post('/confirm', [UserController::class, 'confirm']);

    $group->post('/edit', [UserController::class, 'doEdit']);

})->add($app->getContainer()->get(AdminMiddleware::class));
