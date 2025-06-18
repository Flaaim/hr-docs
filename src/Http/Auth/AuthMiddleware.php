<?php

declare(strict_types=1);

namespace App\Http\Auth;

use App\Http\JsonResponse;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

class AuthMiddleware implements MiddlewareInterface
{
    private SessionInterface $session;
    private bool $isApiRoute;
    public function __construct(SessionInterface $session, bool $isApiRoute = false)
    {
        $this->session = $session;
        $this->isApiRoute = $isApiRoute;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->session->get('user');
        if (!$user) {
            return $this->handleUnauthorized($request);
        }

        return $handler->handle($request->withAttribute('user', $user));
    }

    protected function handleUnauthorized(ServerRequestInterface $request): ResponseInterface
    {
        if($this->isApiRoute || $request->getHeaderLine('Accept') === 'application/json') {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Необходимо авторизоваться на сайте'
            ], 401);
        }

        $response = (new ResponseFactory())->createResponse();
        return $response->withStatus(302)->withHeader('Location', '/');
    }
}
