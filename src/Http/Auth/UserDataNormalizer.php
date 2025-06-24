<?php

namespace App\Http\Auth;

class UserDataNormalizer
{
    public function normalize(string $provider, array $userData): array
    {
        return match ($provider) {
            'google' => $this->normalizeGoogleData($userData),
            'yandex' => $this->normalizeYandexData($userData),
            default => throw new \InvalidArgumentException("Неизвестный провайдер: {$provider}")
        };
    }

    private function normalizeGoogleData(array $userData): array
    {
        return [
            'id' => $userData['sub'],
            'email' => $userData['email'],
        ];
    }

    private function normalizeYandexData(array $userData): array
    {
        return [
            'id' => $userData['id'],
            'email' => $userData['default_email'],
        ];
    }
}
