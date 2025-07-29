<?php

namespace App\Http\Documents;

use App\Http\Exception\Document\DirectionNotFoundException;
use App\Http\Exception\Document\DocumentNotFoundException;
use App\Http\JsonResponse;
use App\Http\Paginator;
use App\Http\Seo\Helper;
use App\Http\Seo\SeoManager;
use App\Http\Services\Cache;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class DocumentController
{
    private Document $document;
    private DocumentService $service;
    private SeoManager $seo;
    private Cache $cache;
    private LoggerInterface $logger;

    public function __construct(Document $document, DocumentService $service, SeoManager $seo, Cache $cache, LoggerInterface $logger)
    {
        $this->document = $document;
        $this->service = $service;
        $this->seo = $seo;
        $this->cache = $cache;
        $this->logger = $logger;
    }


    public function all(Request $request, Response $response, array $args): Response
    {
        try{
            $documents = $this->cache->cachedGet('documents_', function (){
                return $this->document->getAll();
            }, 3600);
            return new JsonResponse($documents, 200);
        }catch (\Psr\SimpleCache\InvalidArgumentException $e){
            $this->logger->error('Cache error: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
            ]);
            $documents = $this->document->getAll();
            return new JsonResponse($documents, 200);
        }catch(\Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function get(Request $request, Response $response, array $args): Response
    {
        try{
            $document_id = $request->getQueryParams()['document_id'] ?? null;
            if($document_id === null){
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Document ID is required'
                ], 400);
            }
            $document = $this->document->getById($document_id);
            return new JsonResponse($document, 200);
        }catch(\Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function byDirection(Request $request, Response $response, array $args): Response
    {
        try{
            $direction_id = $request->getQueryParams()['direction_id'];
            $documents = $this->document->getByDirection($direction_id);
            return new JsonResponse($documents);
        }catch (\Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function document(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? '';
        try{
            $document = $this->document->getById($id);
            if(empty($document)){
                throw new DocumentNotFoundException();
            }

            $documents = $this->document->getAll([], 6);
            $this->seo->set([
                'title' => 'Скачать '.$document['title'],
                'description' => $description = 'Документ: '.$document['title']. ', формат:' . $document['mime_type'].', обновлен '. date('d.m.Y', $document['updated']). '. Скачать бесплатно в Word',
                'keywords' => Helper::createKeywordsFromTitle($description)
            ]);
            return Twig::fromRequest($request)->render($response, 'pages/documents/document.twig', [
                'document' => $document,
                'documents' => $documents,
            ]);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage(), 500);
        }
        catch (\Throwable $e){
            throw new HttpInternalServerErrorException($request, 'Server error');
        }
    }
    public function documents(Request $request, Response $response, array $args): Response
    {
        $direction_slug = $args['slug'] ?? '';
        try {
            $page = $request->getQueryParams()['page'] ?? 1;
            $itemsPerPage = 25;
            $count = $this->service->getCountByDirectionSlug($direction_slug);
            $paginator = new Paginator($page, $count, $itemsPerPage);

            $result = $this->service->getDocumentsByDirectionSlug(
                $direction_slug,
                $paginator->getItemsPerPage(),
                $paginator->getOffset()
            );
            $this->seo->set([
                'title' => 'Список шаблонов документов по разделу ' . $result['direction']['name'],
                'description' => 'На странице приведены формы (образцы) в виде таблицы (интерактивной, статичной) документов по управлению персоналом (HR-менеджмент) по разделу: '. $result['direction']['name'],
            ]);

            return Twig::fromRequest($request)->render(
                $response,
                'pages/documents/documents.twig',
                [
                    'documents' => $result['documents'],
                    'direction' => $result['direction'],
                    'paginator' => $paginator
                ]
            );
        }catch (DirectionNotFoundException $e){
            throw new HttpNotFoundException($request, $e->getMessage());
        }catch (InvalidArgumentException $e) {
            throw new HttpBadRequestException($request, $e->getMessage());
        }catch (\Exception $e){
            throw new Exception($e->getMessage(), 500);
        }catch (\Throwable $e){
            throw new HttpInternalServerErrorException($request, 'Server error');
        }
    }

    public function findOrphanedFiles(Request $request, Response $response, array $args): Response
    {
        try {
            $orphanedFiles = $this->service->findOrphanedFiles();
            return new JsonResponse($orphanedFiles, 200);
        } catch (DocumentNotFoundException|DirectoryNotFoundException $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (RuntimeException $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function findLostFiles(Request $request, Response $response, array $args): Response
    {
        try{
            $lostFilesNames = $this->service->findLostFilesNames();
            $documents = $this->document->getByInValues('stored_name', $lostFilesNames);
            return new JsonResponse($documents, 200);
        }catch (DocumentNotFoundException|DirectoryNotFoundException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 404);
        }catch (RuntimeException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }catch (\Exception $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }


    }



}
