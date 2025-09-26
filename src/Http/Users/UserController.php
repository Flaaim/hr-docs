<?php

namespace App\Http\Users;

use App\Http\Auth\Auth;
use App\Http\Exception\Auth\UserNotFoundException;
use App\Http\Exception\Subcription\SubscriptionPlanAlreadyUpgradedException;
use App\Http\Exception\Subcription\SubscriptionPlanNotFoundException;
use App\Http\JsonResponse;
use App\Http\Subscription\Subscription;
use InvalidArgumentException;
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
    private UserService $service;
    public function __construct(
        Auth $authModel,
        SessionInterface $session,
        Subscription $subscription,
        User $user,
        UserService $service
    )
    {
        $this->authModel = $authModel;
        $this->session = $session;
        $this->subscription = $subscription;
        $this->user = $user;
        $this->service = $service;
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

    public function get(Request $request, Response $response, array $args): Response
    {
        $user_id = $request->getQueryParams()['user_id'] ?? null;
        try{
            if($user_id === null){
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'User Id is required'
                ], 400);
            }
            $user = $this->user->getById($user_id);
            return new JsonResponse($user);
        }catch (\Exception $e){
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

    public function doEdit(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody() ?? [];
        try{
            $this->service->editUser($data);
            return new JsonResponse(['status' => 'success', 'message' => 'Данные пользователя изменены'], 200);
        }catch(SubscriptionPlanAlreadyUpgradedException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 409);
        }catch(SubscriptionPlanNotFoundException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 404);
        }catch(InvalidArgumentException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }catch (\Exception $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteWithExpired(Request $request, Response $response, array $args): Response
    {
        try{
            $deleted = $this->service->clearUserExpiredRegistrations();
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Users deleted successfully. Count: ' . $deleted
            ], 200);
        }catch (UserNotFoundException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 404);
        }catch (\RuntimeException|\Exception $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }


    }
}
