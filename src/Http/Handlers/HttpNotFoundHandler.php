<?php

namespace App\Http\Handlers;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;

class HttpNotFoundHandler
{
    public function __invoke(Request $request, \Throwable $exception, bool $displayErrorDetails): Response
    {
        if($exception instanceof HttpNotFoundException) {
            $response = new \Slim\Psr7\Response();
            $twig = Twig::fromRequest($request);
            return $twig->render($response, 'errors/404.twig')->withStatus(404);
        }

    }
}
