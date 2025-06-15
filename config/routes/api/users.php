<?php

declare(strict_types=1);


/**
 * @var $app
 */

use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use Slim\Routing\RouteCollectorProxy;

$app->group('/api/users', function (RouteCollectorProxy $group) {
    $group->get('/all', [UserController::class, 'all']);
    $group->post('/confirm', [UserController::class, 'confirm']);
})->add($app->getContainer()->get(AdminMiddleware::class));
