<?php

namespace App\Http\Payment;

use App\Http\Exception\Auth\UserNotFoundException;
use App\Http\Exception\Payment\PaymentCreateFailedException;
use App\Http\Exception\Payment\PaymentEventException;
use App\Http\Exception\Payment\PaymentNotFoundException;
use App\Http\Exception\Payment\PaymentWebhookException;
use App\Http\Exception\Subcription\SubscriptionPlanAlreadyUpgradedException;
use App\Http\Queue\Messages\Email\EmailPaymentMessage;
use App\Http\Subscription\SubscriptionService;
use JsonException;
use Symfony\Component\Messenger\MessageBus;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use YooKassa\Client;
use YooKassa\Common\Exceptions\ApiException;
use YooKassa\Model\CurrencyCode;
use YooKassa\Model\Notification\NotificationEventType;
use YooKassa\Model\Notification\NotificationFactory;
use YooKassa\Model\Payment\ConfirmationType;
use YooKassa\Model\Payment\PaymentInterface;
use YooKassa\Request\Payments\CreatePaymentRequest;

class YooKassaService
{
    const PAYMENT_STATUS_SUCCESS = 'succeeded';
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_FAILED = 'failed';
    public function __construct(
        private readonly Client $client,
        private readonly LoggerInterface $logger,
        private readonly NotificationFactory $notification,
        private readonly Payment $payment,
        private readonly SubscriptionService $subscriptionService,
        private readonly SessionInterface $session,
        private readonly MessageBus $messageBus,
    )
    {}
    public function createPayment(array $plan): PaymentInterface
    {
        $user = $this->session->get('user');
        if(empty($user)){
            throw new UserNotFoundException('Пользователь не найден');
        }

        if(!$this->subscriptionService->needsPlanUpdate($user['id'], $plan['slug'])){
            throw new SubscriptionPlanAlreadyUpgradedException('Ошибка. Подписка уже обновлена');
        }
        $metadata = [
            'payment_id' => uniqid(),
            'user_id' => $user['id'],
            'plan_slug' => $plan['slug'],
            'email' => $user['email']
        ];
        $payment = $this->createPaymentDetails($plan, $user, $metadata);

        $paymentData = [
            'yookassa_id' => $payment->getId(),
            'user_id' => $user['id'],
            'payment_id' => $metadata['payment_id'],
            'plan_slug' => $plan['slug'],
            'amount' => $plan['price'],
            'currency' => 'RUB',
            'status' => self::PAYMENT_STATUS_PENDING,
            'description' => $plan['name'],
            'metadata' => json_encode($metadata)
        ];

        $paymentId  = $this->payment->savePayment($paymentData);

        if(!$paymentId){
            throw new PaymentCreateFailedException('Ошибка создания платежа');
        }
        return $payment;
    }
    public function createPaymentDetails(array $plan, array $user, array $metadata): PaymentInterface
    {
            $builder = CreatePaymentRequest::builder();
            $builder->setAmount($plan['price'])
                ->setCurrency(CurrencyCode::RUB)
                ->setCapture(true)
                ->setDescription($plan['name'])
                ->setMetadata($metadata);
            $builder->setConfirmation([
                'type' => ConfirmationType::REDIRECT,
                'returnUrl' => $_ENV['APP_PATH'].'/payment/return?payment_id=' . $metadata['payment_id']
            ]);
            $builder->setReceiptEmail($user['email']);
            $request = $builder->build();
            return $this->client->createPayment($request,
                uniqid('', true)
            );
    }

    public function checkPayment(string $payment_id): array
    {
        $paymentData = $this->payment->findByPaymentId($payment_id);
        if(empty($paymentData)){
            throw new PaymentNotFoundException('Данные платежа на найдены');
        }
        return match ($paymentData['status']) {
            'succeeded' => [
                'status' => 'succeeded',
                'message' => 'Платеж успешно проведен. Ваш план подписки обновлен.',
                'icon' => 'success'
            ],
            'pending' => [
                'status' => 'pending',
                'message' => 'Платеж обрабатывается. Обычно это занимает 1-5 минут.',
                'payment_id' => $payment_id,
                'icon' => 'pending'
            ],
            'canceled' => [
                'status' => 'canceled',
                'message' => 'Платеж отменен. Попробуйте оплатить снова.',
                'error_code' => 'PAYMENT_CANCELED',
                'icon' => 'error'
            ],
            default => [
                'status' => 'unknown',
                'message' => 'Статус платежа не распознан. Пожалуйста, проверьте позже.',
                'error_code' => 'UNKNOWN_STATUS',
                'icon' => 'warning'
            ],
        };
    }

    public function handleWebhook(string $requestBody): void
    {
        try {
            $data = json_decode($requestBody, true, 512, JSON_THROW_ON_ERROR);
            $notification = $this->notification->factory($data);
            $paymentObject = $notification->getObject();
            $paymentId = $paymentObject->getId();
            $eventType = $notification->getEvent();

            $this->logger->info('Received webhook', [
                'payment_id' => $paymentId,
                'event_type' => $eventType,
                'raw_data' => $data
            ]);

            $payment = $this->client->getPaymentInfo($paymentId);

            switch ($eventType) {
                case NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE:
                case NotificationEventType::PAYMENT_SUCCEEDED:
                    $this->handleSuccessfulPayment($payment);
                    break;
                case NotificationEventType::PAYMENT_CANCELED:
                    $this->handleCanceledPayment($payment);
                    break;
                default:
                    $this->logger->warning('Unsupported event type', [$eventType]);
                    throw new PaymentEventException('Unsupported event type: ' . $eventType);
            }
        }catch(JsonException $e){
            $this->logger->error('JSON parsing error', ['error' => $e->getMessage()]);
            throw new PaymentWebhookException('Invalid JSON data: ' . $e->getMessage());
        }catch(ApiException $e){
            $this->logger->error('YooKassa API error', [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId ?? null
            ]);
            throw new PaymentWebhookException('API error: ' . $e->getMessage());
        }catch (Throwable $e) {
            $this->logger->error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new PaymentWebhookException('Webhook processing failed: ' . $e->getMessage());
        }
    }
    private function handleSuccessfulPayment(PaymentInterface $payment): void
    {
        if($payment->getStatus() !== self::PAYMENT_STATUS_SUCCESS){
            $this->logger->warning('Payment status mismatch', [
                'payment_id' => $payment->getId(),
                'expected_status' => self::PAYMENT_STATUS_SUCCESS,
                'actual_status' => $payment->getStatus()
            ]);
            return;
        }
        $metadata = $payment->getMetadata();

        if (empty($metadata['user_id']) || empty($metadata['plan_slug']) || empty($metadata['email'])) {
            $this->logger->error('Missing required metadata', [
                'payment_id' => $payment->getId(),
                'metadata' => $metadata
            ]);
            throw new RuntimeException('Required metadata is missing');
        }
        try{
            $this->subscriptionService->upgradePlan(
                (int)$metadata['user_id'],
                (string) $metadata['plan_slug']
            );
            $this->payment->updatePaymentStatus($payment->getId(),
                self::PAYMENT_STATUS_SUCCESS
            );
            $this->messageBus->dispatch(new EmailPaymentMessage(
                $metadata['email'],
                'Оплата подписки',
                $payment->getAmount()->getValue(),
                $metadata['plan_slug']
            ));

            $this->logger->info('Subscription upgraded successfully', [
                'payment_id' => $payment->getId(),
                'user_id' => $metadata['user_id'],
                'plan_slug' => $metadata['plan_slug']
            ]);
        }catch (Throwable $e){
            $this->logger->error('Failed to upgrade subscription', [
                'payment_id' => $payment->getId(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function handleCanceledPayment(PaymentInterface $payment): void
    {
        $this->payment->updatePaymentStatus($payment->getId(),
            self::PAYMENT_STATUS_FAILED
        );
        $this->logger->info('Payment marked as canceled', [
            'payment_id' => $payment->getId(),
            'status' => $payment->getStatus()
        ]);
    }

}
