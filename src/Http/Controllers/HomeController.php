<?php

namespace App\Http\Controllers;

use App\Http\Documents\Document;
use App\Http\Exception\Sitemap\SitemapNotFoundException;
use App\Http\Seo\Sitemap\Sitemap;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;

class HomeController
{
    private Document $document;
    private Sitemap $sitemap;
    private LoggerInterface $logger;

    public function __construct(Document $document, Sitemap $sitemap, LoggerInterface $logger)
    {
        $this->document = $document;
        $this->sitemap = $sitemap;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        try{
            $documents = $this->document->getAll([], 9);
            return Twig::fromRequest($request)->render($response, 'pages/home.twig',
                ['documents' => $documents])
                ->withHeader('Content-Type', 'text/html');
        }catch (\Throwable $e){
            throw new HttpInternalServerErrorException($request, $e->getMessage());
        }
    }

    public function sitemap(Request $request, Response $response, array $args): Response
    {
        try{
            $this->sitemap->generateDocumentIds($this->document->getDocumentsIds());
            $xmlContent = $this->sitemap->generate();
            $response->getBody()->write($xmlContent);
            return $response->withHeader('Content-Type', 'application/xml');
        }catch (SitemapNotFoundException $e){
            throw new HttpNotFoundException($request, $e->getMessage());
        }catch (InvalidArgumentException $e){
            throw new HttpInternalServerErrorException($request, $e->getMessage());
        }catch (\Throwable $e) {
            $this->logger->error("Sitemap error: " . $e->getMessage());
            throw new HttpInternalServerErrorException($request, "Sitemap generation failed");
        }
    }

    public function terms(Request $request, Response $response, array $args): Response
    {
        try{
            return Twig::fromRequest($request)->render($response, 'pages/terms.twig')
                ->withHeader('Content-Type', 'text/html');
        }catch (\Throwable $e){
            throw new HttpInternalServerErrorException($request, $e->getMessage());
        }
    }
}
