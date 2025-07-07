<?php

namespace App\Http\Documents\Preview;


use App\Http\Exception\Document\DocumentNotFoundException;
use App\Http\Exception\Document\DocumentWrongTypeException;
use App\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;

class DocumentPreviewController
{
    private DocumentPreviewService $service;
    public function __construct(DocumentPreviewService $service)
    {
        $this->service = $service;
    }

    public function doPreview(Request $request, ResponseInterface $response, array $args): Response
    {
        $document_id = $request->getParsedBody()['document_id'] ?? null;
        try{
            if($document_id === null) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid document id'
                ], 400);
            }
            $protectedHtml = $this->service->previewDocument($document_id);
            return new JsonResponse($protectedHtml);
        }catch (DocumentNotFoundException $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
        catch (DocumentWrongTypeException $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 415);
        }
        catch (\Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Внутреняя ошибка сервера'
            ], 500);
        }



    }
}
