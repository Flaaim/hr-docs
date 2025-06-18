<?php

namespace App\Http\Middleware;

use App\Http\JsonResponse;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class VerifyCsrfTokenMiddleware implements MiddlewareInterface
{

    private SessionInterface $session;
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if($request->getMethod() === 'POST'){
            $body = $request->getParsedBody();
            $submittedToken = $body['csrf_token'] ?? '';
            $storedToken = $this->session->get('csrf_token');
        }

        if (empty($submittedToken)){
            return new JsonResponse(['status' => 'error', 'message' => 'CSRF token is missing.'], 403);
        }
        if(!hash_equals($storedToken, $submittedToken)){
            return new JsonResponse(['status' => 'error', 'message' => 'CSRF token is invalid.'], 403);
        }

        return $handler->handle($request);
    }
}
