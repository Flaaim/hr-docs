<?php

namespace App\Http\Middleware;

use App\Http\JsonResponse;
use App\Http\Subscription\Subscription;
use App\Http\Subscription\SubscriptionService;
use PHP_CodeSniffer\Tokenizers\JS;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CheckSubscriptionMiddleware implements MiddlewareInterface
{
    private $subscriptionService;
    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute("user") ?? null;
        if($user === null){
            new JsonResponse(['status' => 'error', 'message' => 'Необходимо авторизоваться на сайте'], 401);
        }
        $this->subscriptionService->checkAndUpdateSubscription($user['id']);
        return $handler->handle($request);
    }
}
