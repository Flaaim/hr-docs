<?php

namespace App\Http\Auth;

use Aego\OAuth2\Client\Provider\Yandex;
use App\Http\Exception\Auth\SocialProviderNotFoundException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Google;

class SocialProvider
{
    public function __construct(
        private readonly Yandex $yandex,
        private readonly Google $google,
    )
    {}
    public function getProvider($name): AbstractProvider
    {
        return match ($name){
            'yandex' => $this->yandex,
            'google' => $this->google,
            default => throw new SocialProviderNotFoundException('Неизвестный провайдер'),
        };
    }
}
