<?php

namespace App\Http\Documents\HandleFile;

use App\Http\JsonResponse;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HandleFileController
{
    public function __construct(private readonly HandleFileService $service)
    {}

    public function handleAction(Request $request, Response $response): Response
    {
        $fileData = $request->getAttribute('file_data', []);

        try{
            $affectedRows = $this->service->insertFiles($fileData);
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Файлы успешно загружены/обновлены!',
                'affectedRows' => $affectedRows
            ]);
        }catch (InvalidArgumentException $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }catch (\RuntimeException|\Exception $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function doReload(Request $request, Response $response, array $args): Response
    {
       return $this->handleAction($request, $response);
    }

    public function doUpload(Request $request, Response $response, array $args): Response
    {
        return $this->handleAction($request, $response);
    }
}
