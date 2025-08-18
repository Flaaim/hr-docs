<?php

declare(strict_types=1);

/**
 * @var $app
 */

use App\Http\Admin\AdminController;
use App\Http\Middleware\AdminMiddleware;
use Slim\Routing\RouteCollectorProxy;

$app->group('/admin', function (RouteCollectorProxy $group) {
    $group->get('[/]', [AdminController::class, 'index']);
    $group->get('/check', [AdminController::class, 'check']);
    $group->get('/users', [AdminController::class, 'users']);
    $group->get('/payments', [AdminController::class, 'payments']);
    $group->get('/mailing', [AdminController::class, 'mailing']);
})->add(AdminMiddleware::class);
