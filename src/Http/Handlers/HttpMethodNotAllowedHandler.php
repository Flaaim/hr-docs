<?php

namespace App\Http\Handlers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Views\Twig;

class HttpMethodNotAllowedHandler
{
    public function __invoke(Request $request, \Throwable $exception, bool $displayErrorDetails): Response
    {
        if($exception instanceof HttpMethodNotAllowedException) {
            $response = new \Slim\Psr7\Response();
            $twig = Twig::fromRequest($request);
            return $twig->render($response, 'errors/405.twig')->withStatus(405);
        }
    }
}
