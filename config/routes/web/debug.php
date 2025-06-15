<?php

declare(strict_types=1);

/**
 * @var $app
 */


$app->get('/debug/routes', function ($request, $response) use ($app) {
    $routes = $app->getRouteCollector()->getRoutes();


    $result = [];
    foreach ($routes as $route) {
        $result[] = [
            'methods' => $route->getMethods(),
            'pattern' => $route->getPattern(),
            'name' => $route->getName(),
            'callable' => $route->getCallable(),
            //'middleware' => $route->getMiddleware()
        ];
    }

    return new \App\Http\JsonResponse($result);
});
