<?php

namespace Auth;

use App\Http\Auth\Auth;
use App\Http\Auth\AuthService;
use App\Http\Services\Mail\Mail;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class VerifyTest extends TestCase
{
    private string $email;
    private string $token;
    private AuthService $authService;

    public function setUp(): void
    {
        $this->email = 'email@email.com';
        $this->token = 'some_token';
        $this->mockUser = $this->createMock(Auth::class);
        $this->mockMail = $this->createMock(Mail::class);
        $this->authService = new AuthService($this->mockUser, $this->mockMail);
    }

    public function testVerifyFailedTokenIsNull()
    {
        $this->expectException(RuntimeException::class);
        $this->authService->verifiedUser(null);
    }
    public function testVerifyFailedTokenIsEmpty()
    {
        $this->expectException(RuntimeException::class);
        $this->authService->verifiedUser('');
    }

    public function testVerifyFailedTokenNotFound()
    {
        $this->mockUser->method('findVerifyToken')->willReturn(false);
        $this->expectException(RuntimeException::class);
        $this->authService->verifiedUser($this->token);
    }
}
