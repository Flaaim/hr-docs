<?php

namespace App\Http\Auth;

use Aego\OAuth2\Client\Provider\Yandex;
use App\Http\Exception\SocialAuthException;
use App\Http\Exception\SocialProviderNotFoundException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Odan\Session\SessionInterface;

class SocialAuthService
{
    private array $config;
    private SessionInterface $session;
    private AuthService $authService;
    private UserDataNormalizer $normalizer;
    public function __construct(array $oauthConfig,
                                SessionInterface $session,
                                AuthService $authService,
                                UserDataNormalizer $normalizer){
        $this->config = $oauthConfig;
        $this->session = $session;
        $this->authService = $authService;
        $this->normalizer = $normalizer;
    }

    public function getProvider(string $provider): AbstractProvider
    {
        return match ($provider){
            'google' => new Google([
                'clientId' => $this->config['google']['clientId'],
                'clientSecret' => $this->config['google']['clientSecret'],
                'redirectUri' => $this->config['google']['redirectUri'],
            ]),
            'yandex' => new Yandex([
                'clientId' => $this->config['yandex']['clientId'],
                'clientSecret' => $this->config['yandex']['clientSecret'],
                'redirectUri' => $this->config['yandex']['redirectUri'],
            ]),
            default => throw new SocialProviderNotFoundException('Неизвестный провайдер'),
        };
    }

    public function handleSocialCallback(array $queryParams, string $providerName): void
    {
        $this->validateState($queryParams['state'] ?? '');
        $provider = $this->getProvider($providerName);
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

    private function loginSocialUser(array $user)
    {

    }
}
