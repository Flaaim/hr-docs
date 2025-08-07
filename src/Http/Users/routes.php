<?php

declare(strict_types=1);


/**
 * @var $app
 */

use App\Http\Auth\AuthMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Users\UserController;
use Slim\Routing\RouteCollectorProxy;
/* API */
$app->group('/api/users', function (RouteCollectorProxy $group) {

    $group->get('/all', [UserController::class, 'all']);
    $group->get('/get', [UserController::class, 'get']);
    $group->post('/confirm', [UserController::class, 'confirm']);
    $group->post('/edit', [UserController::class, 'doEdit']);
    $group->post('/delete-with-expired', [UserController::class, 'deleteWithExpired']);
})->add(AdminMiddleware::class);

$app->group('/user', function (RouteCollectorProxy $group) {
    $group->get('/dashboard', [UserController::class, 'dashboard']);
})->add(AuthMiddleware::class);

