<?php

namespace App\Http\Handlers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpForbiddenException;
use Slim\Views\Twig;

class HttpForbiddenHandler
{
    public function __invoke(Request $request, \Throwable $exception, bool $displayErrorDetails): Response
    {
        if($exception instanceof HttpForbiddenException) {
            $response = new \Slim\Psr7\Response();
            $twig = Twig::fromRequest($request);
            return $twig->render($response, 'errors/403.twig')->withStatus(403);
        }

    }
}
