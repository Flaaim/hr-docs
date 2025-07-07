<?php

namespace App\Http\Handlers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Views\Twig;

class HttpInternalErrorHandler
{
    public function __construct(private readonly LoggerInterface $logger){}
    public function __invoke(Request $request, \Throwable $exception, bool $displayErrorDetails): Response
    {
        if($exception instanceof HttpInternalServerErrorException) {
            $response = new \Slim\Psr7\Response();
            $this->logger->error($exception->getMessage(), [
                'url' => $request->getUri(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);
            $twig = Twig::fromRequest($request);
            return $twig->render($response, 'errors/500.twig')->withStatus(500);
        }
    }
}
