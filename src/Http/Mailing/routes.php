<?php

declare(strict_types=1);

/**
 * @var $app
 */

use App\Http\Mailing\MailingController;
use Slim\Routing\RouteCollectorProxy;


$app->group('/api/mailing', function (RouteCollectorProxy $group){
    $group->get('/list', [MailingController::class, 'list']);
    $group->post('/send', [MailingController::class, 'send']);


});

$app->group('/mailing', function (RouteCollectorProxy $group){
    $group->get('/unsubscribe', [MailingController::class, 'unsubscribe']);
});

