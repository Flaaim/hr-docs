<?php

namespace App\Http\Payment;

use App\Http\Exception\Payment\PaymentCreateFailedException;
use App\Http\Exception\Payment\PaymentNotFoundException;
use App\Http\Exception\Payment\PaymentWebhookException;
use App\Http\Exception\Subcription\SubscriptionPlanNotFoundException;
use App\Http\JsonResponse;
use App\Http\Subscription\SubscriptionPlan;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Slim\Views\Twig;
use Throwable;

class PaymentController
{
    public function __construct(
        private readonly YooKassaService $service,
        private readonly SubscriptionPlan $plan,
        private readonly LoggerInterface $logger,
        private readonly Payment $payment,
    )
    {}

    public function createPayment(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $plan_slug = (string)($data['slug'] ?? null);
        try{
            $plan = $this->plan->getPlanBySlug($plan_slug);
            if(empty($plan)){
                throw new SubscriptionPlanNotFoundException('План подписки не найден');
            }
            $payment = $this->service->createPayment($plan);
            return new JsonResponse(['status' => 'success', 'redirect_url' => $payment->getConfirmation()->getConfirmationUrl()]);
        }catch(SubscriptionPlanNotFoundException $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 404);
        }catch(PaymentCreateFailedException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }catch (\Exception $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function handleWebhook(Request $request, Response $response, array $args): Response
    {
        try{
            $body = (string)$request->getBody();
            $this->service->handleWebhook($body);
            return new JsonResponse(['status' => 'success'], 200);
        }catch (PaymentWebhookException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }catch (\Exception $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

    }

    public function paymentReturn(Request $request, Response $response, array $args): Response
    {
        $data = $request->getQueryParams();
        $payment_id = $data['payment_id'] ?? [];
        $view = Twig::fromRequest($request);
        try{
            if(empty($payment_id)){
                throw new InvalidArgumentException('Не удалось идентифицировать платеж. Пожалуйста, свяжитесь с поддержкой.');
            }
            $responseData = $this->service->checkPayment($payment_id);
            $responseData['is_success'] = ($responseData['status'] ?? null) === 'succeeded';
            return $view->render($response, 'pages/payment/return.twig', $responseData);
        }catch(InvalidArgumentException|PaymentNotFoundException $e){
            return $view->render($response, 'pages/payment/return.twig', [
                'message' => $e->getMessage(),
                'error_code' => $e instanceof InvalidArgumentException ? 'MISSING_PAYMENT_ID' : 'PAYMENT_NOT_FOUND',
                'is_error' => true
            ]);
        }catch (Throwable  $e){
            $this->logger->error('Payment return error', ['error' => $e->getMessage()]);
            return $view->render($response, 'pages/payment/return.twig', [
                'message' => 'Произошла непредвиденная ошибка',
                'error_code' => 'SYSTEM_ERROR',
                'is_error' => true
            ]);
        }
    }

    public function all(Request $request, Response $response, array $args): Response
    {
        try{
            $users = $this->payment->getAll();
            return new JsonResponse($users, 200);
        }catch(\Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function doDelete(Request $request, Response $response, array $args): Response
    {
        $payment_id = (int)($request->getParsedBody()['payment_id'] ?? 0);
        try{
            $deleted = $this->payment->deletePayment($payment_id);
            if($deleted === 0){
                throw new RuntimeException('Failed to delete payment from database');
            }
            return new JsonResponse([
                'success' => true,
                'message' => 'Delete payment successfully'
            ]);
        }catch(RuntimeException|\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
