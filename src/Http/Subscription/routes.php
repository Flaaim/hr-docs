<?php

declare(strict_types=1);

/**
 * @var $app
 */

use App\Http\Subscription\SubscriptionController;
use Slim\Routing\RouteCollectorProxy;

$app->group('/api/subscriptions', function (RouteCollectorProxy $group) {
    $group->get('/all-with-current', [SubscriptionController::class, 'allWithCurrent']);

    $group->get('/all', [SubscriptionController::class, 'all']);

    $group->post('/upgrade', [SubscriptionController::class, 'upgrade']);
});
