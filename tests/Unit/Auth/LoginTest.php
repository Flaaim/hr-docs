<?php

namespace Auth;

use App\Http\Auth\Auth;
use App\Http\Auth\AuthService;
use App\Http\Exception\Auth\InvalidCredentialsException;
use App\Http\Exception\Auth\UserNotFoundException;
use App\Http\Services\CookieManager;
use App\Http\Services\Mail\Mail;
use Odan\Session\SessionInterface;
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
        $this->sessionMock = $this->createMock(SessionInterface::class);
        $this->mockCookieManager = $this->createMock(CookieManager::class);
        $this->authService = new AuthService(
            $this->mockUser,
            $this->mockMail,
            $this->sessionMock,
            $this->mockCookieManager
        );

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
        $this->expectException(InvalidCredentialsException::class);
        $this->authService->login($this->email, $this->password);

    }

    public function testLoginSuccess(): void
    {
        $this->mockUser->method('findByEmail')->willReturn([
            'email' => $this->email,
            'password_hash' => password_hash($this->password, PASSWORD_DEFAULT),
            'role' => 'admin',
            'created-at' => date('Y-m-d')
        ]);
        $this->sessionMock->expects($this->once())->method('set');
        $this->authService->login($this->email, $this->password);
    }

}
