<?php

declare(strict_types=1);

/**
 * @var $app
 */

use App\Http\Controllers\Admin\AdminController;
use App\Http\Middleware\AdminMiddleware;
use Slim\Routing\RouteCollectorProxy;

$app->group('/admin', function (RouteCollectorProxy $group) {
    $group->get('[/]', [AdminController::class, 'index']);
    $group->get('/users', [AdminController::class, 'users']);

})->add($app->getContainer()->get(AdminMiddleware::class));
