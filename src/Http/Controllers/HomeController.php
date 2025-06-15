<?php

namespace App\Http\Controllers;

use App\Http\Auth\Auth;
use App\Http\Documents\Document;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Views\Twig;

class HomeController
{
    private Auth $userModel;
    private Document $document;

    // Автоматическое внедрение Auth!
    public function __construct(Auth $userModel, Document $document)
    {
        $this->userModel = $userModel;
        $this->document = $document;
    }
    public function index(Request $request, Response $response, array $args): Response
    {
        try{
            $documents = $this->document->getAll([], 9);
            return Twig::fromRequest($request)->render($response, 'pages/home.twig',
                ['documents' => $documents])
                ->withHeader('Content-Type', 'text/html');
        }catch (\Throwable $e){
            throw new HttpInternalServerErrorException($request, 'Server error');
        }
    }
}
