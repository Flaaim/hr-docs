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
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function send(Request $request, Response $response, array $args): Response
    {
        $subject = $request->getParsedBody()['subject'] ?? null;
        $text = $request->getParsedBody()['text'] ?? null;
        try {
            $users = $this->service->getOnlyActiveUsers();
            if(empty($users)){
                return new JsonResponse(['status' => 'error', 'message' => 'No active users found.'], 404);
            }
            if($subject === null || $text === null){
                return new JsonResponse(['status' => 'error', 'message' => 'Name and text are required.'], 400);
            }
            $this->service->sendUpdates($users, $text, $subject);
            return new JsonResponse(['status' => 'success', 'message' => 'Рассылка успешно направлена!']);
        }catch (\InvalidArgumentException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
