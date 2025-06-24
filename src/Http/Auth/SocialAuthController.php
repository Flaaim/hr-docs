<?php

namespace App\Http\Auth;

use App\Http\Exception\Auth\SocialProviderNotFoundException;
use App\Http\JsonResponse;
use Odan\Session\SessionInterface;
use PHPUnit\Event\RuntimeException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;


class SocialAuthController
{
    public function __construct(
        private readonly SocialAuthService $socialService,
        private readonly SessionInterface  $session,
        private readonly SocialProvider $socialProvider,
    ) {}

    public function redirectToProvider(Request $request, Response $response, array $args): Response
    {
        try{
            $providerName = $args['provider'] ?? null;
            $provider = $this->socialProvider->getProvider($providerName);

            $authUrl = $provider->getAuthorizationUrl();
            $this->session->set('oauth2state', $provider->getState());

            return $response->withHeader('Location', $authUrl)->withStatus(302);

        }catch (SocialProviderNotFoundException $e){
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 404);
        }
    }

    public function handleProviderCallback(Request $request, Response $response, array $args): Response
    {
        $providerName = $args['provider'] ?? null;
        $queryParams = $request->getQueryParams();
        try{
            $this->socialService->handleSocialCallback($queryParams, $providerName);
            return $response->withHeader('Location', '/')->withStatus(302);
        }catch (RuntimeException $e){
            throw new RuntimeException($e->getMessage(), 401);
        }catch (SocialProviderNotFoundException $e){
            throw new SocialProviderNotFoundException($e->getMessage(), 404);
        }
    }
}
