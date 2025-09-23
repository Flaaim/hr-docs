<?php

namespace App\Http\Subscription;

use App\Http\Exception\Subcription\SubscriptionPlanAlreadyUpgradedException;
use App\Http\Exception\Subcription\SubscriptionPlanNotFoundException;
use App\Http\JsonResponse;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class SubscriptionController
{
    public function __construct(
        private readonly SubscriptionPlan $plan,
        private readonly SubscriptionService $service,
        private readonly SessionInterface $session,
        private readonly Subscription $subscription,
        private readonly LoggerInterface $logger
    )
    {}
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
            $this->logger->warning('Ошибка обновления подписки', [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'exception' => $e->getMessage()
            ]);
            return new JsonResponse(['status' => 'error', 'message' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    public function getEternalSubscription(Request $request, Response $response, array $args): Response
    {
        try{
            $user = $this->session->get('user');
            if(empty($user)){
                return $response->withHeader('Location', '/login')->withStatus(302);
            }
            $plans = $this->plan->getEternalPlan();
            $current_plan = $this->subscription->getCurrentPlan($user['id']);
            return new JsonResponse(['plans' => $plans, 'current_plan' => $current_plan]);
        }catch (\Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
