<?php

namespace App\Http\Mailing;

use App\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MailingController
{
    public function __construct(private readonly MailingService $service)
    {}
    public function list(Request $request, Response $response, array $args): Response
    {
        try {
            $usersMailingList = $this->service->getAll();
            return new JsonResponse($usersMailingList);
        } catch (\Exception $e) {

        }
    }
}
