<?php

namespace App\Http\Controllers;

use App\Http\Auth\Auth;
use App\Http\Documents\Document;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Views\Twig;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;

class HomeController
{
    private Auth $userModel;
    private Document $document;
    private DoctrineTransport $transport;

    public function __construct(Auth $userModel, Document $document, DoctrineTransport $transport)
    {
        $this->userModel = $userModel;
        $this->document = $document;
        $this->transport = $transport;
    }
    public function index(Request $request, Response $response, array $args): Response
    {
        try{
            $this->transport->get(); // Проверяем подключение
            print_r($this->transport->getMessageCount());
            $documents = $this->document->getAll([], 9);
            return Twig::fromRequest($request)->render($response, 'pages/home.twig',
                ['documents' => $documents])
                ->withHeader('Content-Type', 'text/html');
        }catch (\Throwable $e){
            throw new HttpInternalServerErrorException($request, $e->getMessage());
        }
    }
}
