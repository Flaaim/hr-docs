<?php

declare(strict_types=1);

use App\Http\Auth\AuthController;
use App\Http\Auth\SocialAuthController;
use App\Http\Middleware\ValidationMiddlewareFactory;
use App\Http\Middleware\VerifyCsrfTokenMiddleware;
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
        ]))
        ->add(VerifyCsrfTokenMiddleware::class);


    $group->post('/register', [AuthController::class, 'doRegister'])
        ->add(ValidationMiddlewareFactory::create([
        'email' => v::email()->notEmpty(),
        'password' => v::stringType()->length(6, 12),
        'confirm_password' => v::notEmpty()->equals($_POST['password'] ?? ''),
    ], [
        'email' => 'Неверный формат email',
        'password' => 'Пароль должен быть от 6 до 12 символов',
        'confirm_password' => 'Пароли не совпадают'
    ]))
        ->add(VerifyCsrfTokenMiddleware::class);;

    $group->post('/requestReset', [AuthController::class, 'requestResetPassword'])
        ->add(ValidationMiddlewareFactory::create([
            'email' => v::email()->notEmpty(),
        ]))
        ->add(VerifyCsrfTokenMiddleware::class);

    $group->post('/updatePassword', [AuthController::class, 'doUpdatePassword'])
        ->add(ValidationMiddlewareFactory::create(
            [
                'newPassword' => v::stringType()->length(6, 12),
                'confirmNewPassword' => v::notEmpty()->equals($_POST['newPassword'] ?? ''),
            ],
            [
                'newPassword' => 'Пароль должен быть от 6 до 12 символов',
                'confirmNewPassword' => 'Пароли не совпадают'
            ]
        ))
        ->add(VerifyCsrfTokenMiddleware::class);

    $group->post('/logout', [AuthController::class, 'doLogout']);

    $group->get('/checkRememberMe', [AuthController::class, 'checkRememberMe']);
});

$app->group('/auth', function (RouteCollectorProxy $group) {
    $group->get('/verify', [AuthController::class, 'doVerify']);

    $group->get('/reset', [AuthController::class, 'resetPassword']);



    $group->get('/{provider}', [SocialAuthController::class, 'redirectToProvider']);
    $group->get('/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);
});
