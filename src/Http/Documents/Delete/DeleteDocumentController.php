<?php

namespace App\Http\Documents\Delete;

use App\Http\Exception\Document\DocumentNotFoundException;
use App\Http\JsonResponse;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

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
            return new JsonResponse([
                'success' => true,
                'message' => 'Delete document successfully'
            ]);
        }catch (DocumentNotFoundException|InvalidArgumentException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
        } catch (\Exception $e){
            return new JsonResponse([ 'status' => 'error' , 'message' => $e->getMessage()], 500);
        }
    }

    public function doDeleteOrphanedFile(Request $request, Response $response, array $args): Response
    {
        $filename = $request->getParsedBody()['filename'] ?? null;
        try{
            if($filename === null){
                throw new InvalidArgumentException('Filename is required');
            }
            if($this->service->deleteOrphanedDocument($filename)){
                return new JsonResponse(['status' => 'success',  'message' => 'Delete document successfully']);
            };
            return new JsonResponse(['status' => 'error',  'message' => 'Delete document failed']);
        }catch (DirectoryNotFoundException|DocumentNotFoundException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 404);
        }catch (InvalidArgumentException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }catch (RuntimeException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
