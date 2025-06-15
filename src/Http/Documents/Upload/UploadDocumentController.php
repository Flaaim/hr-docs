<?php

namespace App\Http\Documents\Upload;

use App\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UploadDocumentController
{
    private UploadDocumentService $service;
    public function __construct(UploadDocumentService $service)
    {
        $this->service = $service;
    }

    public function doUpload(Request $request, Response $response, array $args): Response
    {
        $fileData = $request->getAttribute('file_data', []);
        try{
            $affectedRows = $this->service->insertFiles($fileData);
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Файлы успешно загружены!',
                'affectedRows' => $affectedRows
            ]);
        }catch (\InvalidArgumentException $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }catch (\RuntimeException  $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        } catch (\Exception $e){
            return new JsonResponse([ 'status' => 'error' , 'message' => $e->getMessage()], 500);
        }
    }
}
