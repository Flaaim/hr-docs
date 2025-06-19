<?php

declare(strict_types=1);

use App\Http\JsonResponse;
use Odan\Session\SessionInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var $app
 */
$app->group('/api/csrf', function (RouteCollectorProxy $group) use($app){
    $group->get('/get', function (Request $request, Response $response) {
        $session = $this->get(SessionInterface::class);
        if (!$session->has('csrf_token')) {
            // Генерируем токен, если его нет
            $session->set('csrf_token', bin2hex(random_bytes(32)));
        }
        return new JsonResponse([
            'token' => $session->get('csrf_token')
        ]);
    });
});

