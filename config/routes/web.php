<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use Slim\App;

return static function (App $app): void {
    $routesFiles = glob(__DIR__ . '/web/*.php');

    foreach ($routesFiles as $file) {
        require $file;
    }

    $app->get('/', [HomeController::class, 'index']);
};
