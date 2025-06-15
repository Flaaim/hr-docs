<?php

namespace App\Http\Controllers;

use App\Http\Auth\Auth;
use App\Http\JsonResponse;
use App\Http\Models\User;
use App\Http\Subscription\Subscription;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Views\Twig;

class UserController
{
    private Auth $authModel;
    private SessionInterface $session;
    private Subscription $subscription;
    private User $user;
    public function __construct(
        Auth $authModel,
        SessionInterface $session,
        Subscription $subscription,
        User $user
    )
    {
        $this->authModel = $authModel;
        $this->session = $session;
        $this->subscription = $subscription;
        $this->user = $user;
    }

    public function dashboard(Request $request, Response $response, array $args): Response
    {
        try{
            $user = $this->session->get('user');

            if (empty($user)) {
                return $response->withHeader('Location', '/login')->withStatus(302);
            }
            $current_plan = $this->subscription->getCurrentPlan($user['id']);
            return Twig::fromRequest($request)->render($response, 'pages/user/dashboard.twig', [
                'user' => $user,
                'plan' => $current_plan
            ]);
        }catch (\Throwable $e){
            throw new HttpInternalServerErrorException($request, 'Server error');
        }
    }

    public function all(Request $request, Response $response, array $args): Response
    {
        try{
            $users = $this->user->getAll();
            return new JsonResponse($users, 200);
        }catch(\Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function confirm(Request $request, Response $response, array $args): Response
    {
        try{
            $user_id = (int)$request->getParsedBody()['user_id'] ?? 0;
            $rows = $this->user->confirmUser($user_id);
            if($rows === 0){
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'User not confirm'
                ], 404);
            }
            return new JsonResponse([
                'status' => 'success',
                'message' => 'User successfully confirmed'
            ], 200);
        }catch (\Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
