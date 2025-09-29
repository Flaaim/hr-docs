<?php

namespace App\Http\Log;

use App\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LogController
{
    public function __construct(private readonly LogFile $logFile)
    {}
    public function get(Request $request, Response $response, array $args): Response
    {
        try{
            $log = $this->logFile->get();
            return new JsonResponse($log, 200);
        }catch (\Exception $e){
            return new JsonResponse(['status' => 'error', $e->getMessage()], 500);
        }
    }
}
