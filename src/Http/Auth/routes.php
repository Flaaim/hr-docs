<?php

declare(strict_types=1);

use App\Http\Auth\AuthController;
use App\Http\Middleware\ValidationMiddlewareFactory;
use Respect\Validation\Validator as v;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var $app
 */
$app->group('/api/auth', function (RouteCollectorProxy $group) {
    $group->post('/login', [AuthController::class, 'doLogin'])
        ->add(ValidationMiddlewareFactory::create([
            'email' => v::email()->notEmpty(),
            'password' => v::stringType()->length(6, 12)
        ], [
            'email' => 'Неверный формат email',
            'password' => 'Пароль должен быть от 6 до 12 символов'
        ]));
    $group->post('/register', [AuthController::class, 'doRegister'])->add(ValidationMiddlewareFactory::create([
        'email' => v::email()->notEmpty(),
        'password' => v::stringType()->length(6, 12),
        'confirm_password' => v::notEmpty()->equals($_POST['password'] ?? ''),
    ], [
        'email' => 'Неверный формат email',
        'password' => 'Пароль должен быть от 6 до 12 символов',
        'confirm_password' => 'Пароли не совпадают'
    ]));

    $group->post('/reset', [AuthController::class, 'doReset'])->add(ValidationMiddlewareFactory::create([
        'email' => v::email()->notEmpty(),
    ]));
    $group->post('/logout', [AuthController::class, 'doLogout']);
});

$app->group('/auth', function (RouteCollectorProxy $group) {
    $group->get('/verify', [AuthController::class, 'doVerify']);
    $group->get('/reset', [AuthController::class, 'doResetPassword']);
});
