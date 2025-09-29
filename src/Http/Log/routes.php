<?php

declare(strict_types=1);

/**
 * @var $app
 */

use App\Http\Log\LogController;
use App\Http\Middleware\AdminMiddleware;
use Slim\Routing\RouteCollectorProxy;

$app->group('/api/logs', function (RouteCollectorProxy $group) {
    $group->get('/get', [LogController::class, 'get']);
})->add(AdminMiddleware::class);;
