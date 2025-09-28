<?php

namespace App\Http\ErrorHandler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Handlers\ErrorHandler;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Views\Twig;


class LogErrorHandler extends ErrorHandler
{
    protected LoggerInterface $logger;
    private Twig $twig;
    public function __construct(
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory,
        LoggerInterface $logger,
        Twig $twig
    ) {
        parent::__construct($callableResolver, $responseFactory);
        $this->logger = $logger;
        $this->twig = $twig;
    }

    protected function writeToErrorLog(): void
    {
        $this->logger->error($this->exception->getMessage(), [
            'exception' => $this->exception,
            'url' => (string)$this->request->getUri(),
        ]);
    }

    protected function respond(): \Psr\Http\Message\ResponseInterface
    {

        if ($this->exception instanceof HttpNotFoundException) {
            $response = $this->responseFactory->createResponse(404);
            return $this->twig->render($response, 'errors/404.twig')->withStatus(404);
        }
        if($this->exception instanceof HttpForbiddenException){
            $response = $this->responseFactory->createResponse(403);
            return $this->twig->render($response, 'errors/403.twig')->withStatus(403);
        }
        if($this->exception instanceof HttpMethodNotAllowedException){
            $response = $this->responseFactory->createResponse(405);
            return $this->twig->render($response, 'errors/405.twig')->withStatus(405);
        }
        if($this->exception instanceof HttpInternalServerErrorException){
            $response = $this->responseFactory->createResponse(500);
            return $this->twig->render($response, 'errors/500.twig')->withStatus(500);
        }

        return parent::respond();
    }
}
