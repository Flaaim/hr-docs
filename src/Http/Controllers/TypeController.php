<?php

namespace App\Http\Controllers;

use App\Http\JsonResponse;
use App\Http\Models\Type;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TypeController
{
    private Type $type;

    public function __construct(Type $type){
        $this->type = $type;
    }
    public function types(Request $request, Response $response, array $args): Response
    {
        try{
            $types = $this->type->getAll();
            return new JsonResponse($types);
        }catch (\Exception $e){
            return new JsonResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }
}
