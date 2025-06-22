<?php

namespace Auth;

use App\Http\Auth\Auth;
use App\Http\Auth\AuthService;
use App\Http\Exception\Auth\TokenNotFoundException;
use App\Http\Exception\Auth\UserNotFoundException;
use App\Http\Services\CookieManager;
use App\Http\Services\Mail\Mail;
use InvalidArgumentException;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Unit\Auth\MockFactory;

/**
 * @covers \App\Http\Auth\AuthService
 */
class ResetTest extends TestCase
{
    private string $email;
    private string $password;
    private array $mocks;
    private Auth $auth;

    public function setUp(): void
    {
        $this->email = 'email@email.com';
        $this->password = 'password';
        $this->mocks = MockFactory::create($this);

        $this->auth = $this->mocks[Auth::class];
        $this->mail = $this->mocks[Mail::class];
        $this->session = $this->mocks[SessionInterface::class];
        $this->cookie = $this->mocks[CookieManager::class];

        $this->service = $this->mocks[AuthService::class];
    }

    public function testResetWillThrowNotFoundException(): void
    {
        $this->auth->method('findByEmail')->willReturn([]);
        $this->expectException(UserNotFoundException::class);
        $this->service->reset($this->email);
    }
    public function testResetErrorCreateReset()
    {
        $this->auth->method('findByEmail')->willReturn(['id' => 1, 'email' => $this->email]);
        $this->auth->method('createReset')->willReturn(0);
        $this->expectException(RuntimeException::class);
        $this->service->reset($this->email);
    }

    public function testResetSuccess()
    {
        $this->auth->method('findByEmail')->willReturn([
            'id' => 1, 'email' => $this->email
        ]);
        $this->auth->method('createReset')->willReturn(1);

        $this->mail->method('setTo')->willReturnSelf();
        $this->mail->method('setSubject')->willReturnSelf();
        $this->mail->method('setBodyFromTemplate')->willReturnSelf();

        $this->mail->expects($this->once())
            ->method('send');
        $this->service->reset($this->email);
    }

    public function testUpdatePasswordWillThrowInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->updatePassword(null, '');
    }
    public function testUpdatePasswordWillThrowTokenNotFoundException()
    {
        $this->expectException(TokenNotFoundException::class);
        $this->auth->method('findResetToken')->willReturn([]);
        $this->service->updatePassword('213131', '158754429');
    }

    public function testUpdatePasswordWillThrowTokenNotFoundException_expires()
    {
        $this->expectException(TokenNotFoundException::class);
        $this->expectExceptionMessage('Срок действия токена истек');
        $this->auth->method('findResetToken')->willReturn(['expires' => time() - 100]);
        $this->auth->method('deleteResetToken');
        $this->service->updatePassword('213131', '158754429');
    }

    public function testUpdatePasswordSuccess()
    {
        $this->auth->method('findResetToken')->willReturn(['expires' => time() + 100]);
        $this->auth->method('deleteResetToken');
        $this->auth->expects($this->once())->method('updateUserPassword');
        $this->service->updatePassword('213131', '158754429');
    }
}
