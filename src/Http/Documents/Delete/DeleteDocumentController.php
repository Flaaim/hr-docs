<?php

namespace App\Http\Documents\Delete;

use App\Http\Exception\DocumentNotFoundException;
use App\Http\JsonResponse;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteDocumentController
{
    private DeleteDocumentService $service;

    public function __construct(DeleteDocumentService $service)
    {
        $this->service = $service;
    }
    public function doDelete(Request $request, Response $response, array $args): Response
    {
        $document = $request->getAttribute('document_data', []);
        try{
            $this->service->deleteDocument($document);
        }catch (DocumentNotFoundException|InvalidArgumentException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        } catch (\Exception $e){
            return new JsonResponse([ 'status' => 'error' , 'message' => $e->getMessage()], 500);
        }
        return new JsonResponse([
            'success' => true,
            'message' => 'Delete document successfully'
        ]);
    }
}
