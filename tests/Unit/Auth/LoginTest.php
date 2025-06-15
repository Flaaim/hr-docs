<?php

namespace Auth;

use App\Http\Auth\Auth;
use App\Http\Auth\AuthService;
use App\Http\Exception\UserNotFoundException;
use App\Http\Services\Mail\Mail;
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    private string $email;
    private string $password;
    private AuthService $authService;

    public function setUp(): void
    {
        $this->email = 'email@email.com';
        $this->password = 'password';
        $this->mockUser = $this->createMock(Auth::class);
        $this->mockMail = $this->createMock(Mail::class);
        $this->authService = new AuthService($this->mockUser, $this->mockMail);

    }

    public function testLoginFailedThrowNotFoundException(): void
    {
        $this->mockUser->method('findByEmail')->willReturn([]);
        $this->expectException(UserNotFoundException::class);
        $this->authService->login($this->email, $this->password);
    }

    public function testLoginFailedPasswordVerify(): void
    {
        $this->mockUser->method('findByEmail')->willReturn([
            'email' => $this->email,
            'password_hash' => password_hash('123456', PASSWORD_DEFAULT)
        ]);
        $this->assertFalse($this->authService->login($this->email, $this->password));
    }

    public function testLoginSuccess(): void
    {
        $this->mockUser->method('findByEmail')->willReturn([
            'email' => $this->email,
            'password_hash' => password_hash($this->password, PASSWORD_DEFAULT)
        ]);
        $this->assertIsArray($this->authService->login($this->email, $this->password));
    }
}
