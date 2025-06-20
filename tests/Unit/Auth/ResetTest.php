<?php

namespace Auth;

use App\Http\Auth\Auth;
use App\Http\Auth\AuthService;
use App\Http\Exception\UserNotFoundException;
use App\Http\Services\CookieManager;
use App\Http\Services\Mail\Mail;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ResetTest extends TestCase
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

    public function testResetFailedThrowNotFoundException(): void
    {
        $this->mockUser->method('findByEmail')->willReturn([]);
        $this->expectException(UserNotFoundException::class);
        $this->authService->reset($this->email);
    }

    public function testResetFailed()
    {
        $this->mockUser->method('findByEmail')->willReturn([
            'id' => 1, 'email' => $this->email
        ]);
        $this->mockUser->method('createReset')->willReturn(0);
        $this->expectException(RuntimeException::class);
        $this->authService->reset($this->email);
    }

    public function testResetSuccess()
    {
        $this->mockUser->method('findByEmail')->willReturn([
            'id' => 1, 'email' => $this->email
        ]);
        $this->mockUser->method('createReset')->willReturn(1);

        $this->mockMail->method('setTo')->willReturnSelf();
        $this->mockMail->method('setSubject')->willReturnSelf();
        $this->mockMail->method('setBodyFromTemplate')->willReturnSelf();

        $this->mockMail->expects($this->once())
            ->method('send');
        $this->authService->reset($this->email);
    }
}
