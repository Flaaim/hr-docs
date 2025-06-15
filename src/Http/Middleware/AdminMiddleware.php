<?php

namespace App\Http\Middleware;

use App\Http\Auth\AuthMiddleware;
use App\Http\JsonResponse;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

class AdminMiddleware implements MiddlewareInterface
{
    private SessionInterface $session;
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->session->get('user');

        if (!$user || $user['role'] !== 'admin') {
            $response = (new ResponseFactory())->createResponse();
            return $response->withStatus(302)->withHeader('Location', '/');
            //return new JsonResponse(['status' => 'error', 'errors' => 'Доступ запрещен'], 403);
        }
        return $handler->handle($request);
    }
}
