<?php

namespace App\Http\Controllers;


use App\Http\JsonResponse;
use App\Http\Models\Direction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DirectionController
{

    private Direction $direction;

    public function __construct(Direction $direction)
    {
        $this->direction = $direction;
    }
    public function directions(Request $request, Response $response, array $args): Response
    {
        try{
            $directions = $this->direction->getAll();
            return new JsonResponse($directions);
        }catch (\Exception $e){
            return new JsonResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }
    public function direction()
    {

    }
}
