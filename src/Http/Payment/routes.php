<?php

declare(strict_types=1);

/**
 * @var $app
 */

use App\Http\Auth\AuthMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Payment\PaymentController;
use Odan\Session\SessionInterface;
use Slim\Routing\RouteCollectorProxy;

/* API */
$app->group('/api/payments', function (RouteCollectorProxy $group) use ($app){
    $group->post('/create', [PaymentController::class, 'createPayment'])
        ->add(new AuthMiddleware(
            $app->getContainer()->get(SessionInterface::class),
            true
        ));

    $group->post('/webhook', [PaymentController::class, 'handleWebhook']);

    $group->get('/all', [PaymentController::class, 'all'])->add(AdminMiddleware::class);
    $group->post('/delete', [PaymentController::class, 'doDelete'])->add(AdminMiddleware::class);
});

/* WEB */
$app->group('/payment', function (RouteCollectorProxy $group) use ($app) {
    $group->get('/return', [PaymentController::class, 'paymentReturn'])
        ->add(new AuthMiddleware(
            $app->getContainer()->get(SessionInterface::class),
        ));
});
