<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app): void {

        $routesFiles = glob(__DIR__ . '/api/*.php');
        foreach ($routesFiles as $file) {
            require $file;
        }

        require dirname(__DIR__, 2) . '/src/Http/Auth/routes.php';
        require dirname(__DIR__, 2) . '/src/Http/Documents/routes.php';
        require dirname(__DIR__, 2) . '/src/Http/Subscription/routes.php';
        require dirname(__DIR__, 2) . '/src/Http/Users/routes.php';
};
