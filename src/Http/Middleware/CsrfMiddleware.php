<?php

namespace App\Http\Middleware;

use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    private SessionInterface $session;
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if(!$this->session->has('csrf_token')) {
            $this->session->set('csrf_token', bin2hex(random_bytes(32)));
        }
       return $handler->handle($request->withAttribute('csrf_token', $this->session->get('csrf_token')));
    }
}
