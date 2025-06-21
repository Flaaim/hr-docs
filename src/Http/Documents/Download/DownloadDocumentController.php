<?php

namespace App\Http\Documents\Download;


use App\Http\Exception\Document\DocumentNotFoundException;
use App\Http\Exception\Document\DownloadLimitExceededException;
use App\Http\Exception\Document\FileNotFoundInStorageException;
use App\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Slim\Exception\HttpInternalServerErrorException;

class DownloadDocumentController
{
    private DownloadDocumentService $service;
    private CacheInterface $cache;
    private LoggerInterface $logger;
    public function __construct(DownloadDocumentService $service, CacheInterface $cache, LoggerInterface $logger)
    {
        $this->service = $service;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function getDocument(Request $request, Response $response, array $args): Response
    {
        try{
            $document_id = $request->getParsedBody()['document_id'] ?? null;
            $user = $request->getAttribute('user', []);
            $document = $this->service->getDocument($document_id);
            $this->service->checkDownloadLimit($user['id']);
            $token = bin2hex(random_bytes(32));
            $this->cache->set('doc_'. $token, $document, 300);
            return new JsonResponse([
                'download_url' => '/document/download/' . $token,
                'expires' => time() + 300
            ]);
        }catch (DocumentNotFoundException|FileNotFoundInStorageException $e){
            return new JsonResponse([
                'status' => 'error', 'message' => $e->getMessage()
            ], 404);
        }catch (DownloadLimitExceededException $e){
            return new JsonResponse([
                'status' => 'error', 'message' => $e->getMessage()
            ], 403);
        } catch (\Exception $e){
            return new JsonResponse([
                'status' => 'error', 'message' => $e->getMessage()
            ], 500);
        }
    }
    public function download(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'] ?? null;
        try{
            if (!$token) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Token is required'
                ], 400);
            }
            if (!$document = $this->cache->get('doc_'.$token)) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid token'
                ],404);
            }
            return $this->service->performFileDownload($document);
        }catch (FileNotFoundInStorageException $e){
            throw new FileNotFoundInStorageException($e->getMessage(), 404);
        }catch (\Throwable $e){
            throw new HttpInternalServerErrorException($request, 'Server error');
        }



    }

}
