<?php

namespace App\Http\Subscription;

use App\Http\Exception\SubscriptionPlanAlreadyUpgradedException;
use App\Http\Exception\SubscriptionPlanNotFoundException;
use App\Http\JsonResponse;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SubscriptionController
{
    private SubscriptionPlan $plan;
    private SubscriptionService $service;
    private SessionInterface $session;
    private Subscription $subscription;
    public function __construct(SubscriptionPlan $plan, SubscriptionService $service, SessionInterface $session, Subscription $subscription)
    {
        $this->plan = $plan;
        $this->service = $service;
        $this->session = $session;
        $this->subscription = $subscription;
    }
    public function allWithCurrent(Request $request, Response $response, array $args): Response
    {
        try{
            $user = $this->session->get('user');
            if(empty($user)){
                return $response->withHeader('Location', '/login')->withStatus(302);
            }
            $plans = $this->plan->all();
            $current_plan = $this->subscription->getCurrentPlan($user['id']);
            return new JsonResponse(['plans' => $plans, 'current_plan' => $current_plan]);
        }catch (\Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function all(Request $request, Response $response, array $args): Response
    {
        try {
            $plans = $this->plan->all();
            return new JsonResponse($plans);
        }catch (\Exception $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function upgrade(Request $request, Response $response, array $args): Response
    {
        try{
            $slug = $request->getParsedBody()['slug'] ?? null;
            if($slug == null){
                return new JsonResponse(['status' => 'error', 'message' => 'Не указан план подписки'], 400);
            }
            $user = $this->session->get('user');
            if(empty($user)){
                return $response->withHeader('Location', '/login')->withStatus(302);
            }
            if($this->service->needsPlanUpdate($user['id'], $slug)){
                $this->service->upgradePlan($user['id'], $slug);
            }
            return new JsonResponse(['status' => 'success', 'message' => 'План успешно обновлен'],200);
        }catch (SubscriptionPlanNotFoundException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 404);
        }catch (SubscriptionPlanAlreadyUpgradedException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 409);
        }catch (\InvalidArgumentException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }catch (\Exception $e){
            return new JsonResponse(['status' => 'error', 'message' => 'Внутренняя ошибка сервера'], 500);
        }
    }
}
