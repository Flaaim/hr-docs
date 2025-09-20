<?php

namespace App\Http\Mailing;

use App\Http\JsonResponse;
use App\Http\Seo\SeoManager;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Http\Exception\Auth\UserNotFoundException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Views\Twig;

class MailingController
{
    public function __construct(
        private readonly MailingService $service,
        private readonly SeoManager $seoManager
    )
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

    public function unsubscribe(Request $request, Response $response, array $args): Response
    {
        $token = $request->getQueryParams()['token'] ?? null;
        try{
            if ($token === null) {
                throw new InvalidArgumentException('Token is required.', 400);
            }

            $email = $this->service->verifyUnsubscribeToken($token);

            $this->service->unsubscribeUser($email);

            $this->seoManager->set([
                'title' => 'Отписаться от рассылок сайта',
                'description' => 'Страница отписки от рассылок обновления сайта'
            ]);
            return Twig::fromRequest($request)->render(
                $response,
                'pages/mailing/unsubscribe.twig',
                ['success' => true, 'email' => $email]
            );
        }catch (InvalidArgumentException $e){
            throw new InvalidArgumentException($e->getMessage(), 400);
        }catch (UserNotFoundException $e){
           throw new UserNotFoundException($e->getMessage(), 404);
        } catch(\Exception $e){
            throw new \Exception($e->getMessage(), 500);
        }
    }
}
