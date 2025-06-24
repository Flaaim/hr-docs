<?php

namespace App\Http\Auth;

use Aego\OAuth2\Client\Provider\Yandex;
use App\Http\Exception\Auth\SocialAuthException;
use App\Http\Exception\Auth\SocialProviderNotFoundException;
use DI\Attribute\Inject;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Odan\Session\SessionInterface;

class SocialAuthService
{
    private SocialProvider $socialProvider;
    private SessionInterface $session;
    private AuthService $authService;
    private UserDataNormalizer $normalizer;
    public function __construct(SocialProvider $socialProvider,
                                SessionInterface $session,
                                AuthService $authService,
                                UserDataNormalizer $normalizer){
        $this->socialProvider = $socialProvider;
        $this->session = $session;
        $this->authService = $authService;
        $this->normalizer = $normalizer;
    }

    public function handleSocialCallback(array $queryParams, string $providerName): void
    {
        $this->validateState($queryParams['state'] ?? '');
        $provider = $this->socialProvider->getProvider($providerName);
        $token = $this->getAccessToken($provider, $queryParams['code'] ?? '');
        $socialUser = $provider->getResourceOwner($token);
        $userData = $this->normalizeUserData($providerName, $socialUser->toArray());
        $user = $this->authService->registerOrLoginSocialUser(
            $providerName,
            $userData['id'],
            $userData['email']
        );
        $this->session->set('user', [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
        ]);

    }

    private function normalizeUserData(string $provider, array $userData): array
    {
        return $this->normalizer->normalize($provider, $userData);
    }
    private function getAccessToken(AbstractProvider $provider, string $code): AccessTokenInterface
    {
        try {
            return $provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
        } catch (IdentityProviderException $e) {
            throw new SocialAuthException(
                'Ошибка получения токена: ' . $e->getMessage(),
                400
            );
        }
    }

    private function validateState(string $state): void
    {
        if (empty($state) || $state !== $this->session->get('oauth2state')) {
            $this->session->remove('oauth2state');
            throw new InvalidStateException('Невалидный state-параметр');
        }
    }
}
