<?php

namespace Auth;

use App\Http\Auth\Auth;
use App\Http\Auth\AuthService;
use App\Http\Exception\UserAlreadyExistsException;
use App\Http\Services\Mail\Mail;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RegisterTest extends TestCase
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

    public function testRegisterFailedThrowUserAlreadyExistsException(): void
    {
        $this->mockUser->method('findByEmail')->willReturn([$this->email]);
        $this->expectException(UserAlreadyExistsException::class);
        $this->authService->register($this->email, $this->password);

    }
    public function testRegisterFailedThrowRuntimeException()
    {

        $this->mockUser->method('findByEmail')->willReturn([]);
        $this->mockUser->method('createUser')->willReturn(false);
        $this->expectException(RuntimeException::class);
        $this->authService->register($this->email, $this->password);
    }
    public function testRegisterSuccess(): void
    {
        $this->mockUser->method('findByEmail')->willReturn([]);
        $this->mockUser->method('createUser')->willReturn(true);

        $this->mockMail->method('setTo')->willReturnSelf();
        $this->mockMail->method('setSubject')->willReturnSelf();
        $this->mockMail->method('setBodyFromTemplate')->willReturnSelf();

        $this->mockMail->expects($this->once())
            ->method('send');
        $this->authService->register($this->email, $this->password);
    }
}
