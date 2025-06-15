<?php

namespace App\Http\Controllers;

use App\Http\JsonResponse;
use App\Http\Models\Section;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SectionController
{
    private Section $section;
    public function __construct(Section $section)
    {
        $this->section = $section;
    }

    public function sections(Request $request, Response $response, array $args): Response
    {
        try{
            $sections = $this->section->getAllByDirectionId($request->getQueryParams()['direction_id']);
            return new JsonResponse($sections);
        }catch (\Exception $e){
            return new JsonResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    public function section(Request $request, Response $response, array $args): Response
    {
        try{
            $section = $this->section->getById($request->getQueryParams()['id']);
            return new JsonResponse($section);
        }catch (\Exception $e){
            return new JsonResponse(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }
}
