<?php

declare(strict_types=1);

/**
 * @var $app
 */

use App\Http\Auth\AuthMiddleware;
use App\Http\Payment\PaymentController;
use Odan\Session\SessionInterface;
use Slim\Routing\RouteCollectorProxy;

$app->group('/api/payment', function (RouteCollectorProxy $group) use ($app){
    $group->post('/create', [PaymentController::class, 'createPayment'])
        ->add(new AuthMiddleware(
            $app->getContainer()->get(SessionInterface::class),
            true
        ));

    $group->post('/webhook', [PaymentController::class, 'handleWebhook']);
});

$app->group('/payment', function (RouteCollectorProxy $group) use ($app) {
    $group->get('/return', [PaymentController::class, 'paymentReturn'])
        ->add(new AuthMiddleware(
            $app->getContainer()->get(SessionInterface::class),
        ));
});
