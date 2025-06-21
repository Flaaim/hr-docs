<?php

namespace App\Http\Middleware;

use App\Http\Auth\Auth;
use App\Http\Auth\AuthService;
use App\Http\Exception\Auth\TokenNotFoundException;
use App\Http\Services\CookieManager;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RememberMeMiddleware implements MiddlewareInterface
{
    private AuthService $authService;
    private Auth $userModel;
    private SessionInterface $session;
    private CookieManager $cookieManager;

    public function __construct(AuthService $authService, Auth $userModel, SessionInterface $session, CookieManager $cookieManager)
    {
        $this->authService = $authService;
        $this->userModel = $userModel;
        $this->session = $session;
        $this->cookieManager = $cookieManager;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cookies = $request->getCookieParams();
        if (!$this->session->has('user') && isset($cookies['remember_token'])) {

            $user = $this->userModel->findByToken($cookies['remember_token']);

            if(empty($user)){
                $this->cookieManager->delete('remember_token');
                throw new TokenNotFoundException('Токен пользователя не найден');
            }

            $this->session->set('user', [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'created_at' => $user['created_at'],
            ]);
        }
        return $handler->handle($request);
    }
}
