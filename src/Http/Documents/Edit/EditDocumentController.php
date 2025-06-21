<?php

namespace App\Http\Documents\Edit;

use App\Http\Documents\Document;
use App\Http\Exception\Document\SectionNotFoundException;
use App\Http\Exception\Document\TypeNotFoundException;
use App\Http\JsonResponse;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EditDocumentController
{
    private Document $document;
    private EditDocumentService $service;
    public function __construct(Document $document, EditDocumentService $service)
    {
        $this->document = $document;
        $this->service = $service;
    }

    public function doEdit(Request $request, Response $response, array $args): Response
    {
        try{
            $data = $request->getParsedBody() ?? [];
            $this->service->editDocument($data);
            return new JsonResponse(['status'=> 'success', 'message' => 'Документ изменен'], 200);
        }catch (TypeNotFoundException|SectionNotFoundException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage(), 404]);
        } catch(InvalidArgumentException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }


    }
}
