<?php

namespace Tests\Unit\Auth;

use App\Http\Auth\Auth;
use App\Http\Auth\AuthService;
use App\Http\Services\CookieManager;
use App\Http\Services\Mail\Mail;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MockFactory
{
    public static function createAuthMock(TestCase $testCase): MockObject
    {
        return $testCase->getMockBuilder(Auth::class)->disableOriginalConstructor()->getMock();
    }
    public static function createMailMock(TestCase $testCase): MockObject
    {
        return $testCase->getMockBuilder(Mail::class)->disableOriginalConstructor()->getMock();
    }
    public static function createSessionMock(TestCase $testCase): MockObject
    {
        return $testCase->getMockBuilder(SessionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
    public static function createCookieManagerMock(TestCase $testCase): MockObject
    {
        return $testCase->getMockBuilder(CookieManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public static function create(TestCase $testCase): array
    {
        $mocks  = [
            Auth::class => self::createAuthMock($testCase),
            Mail::class => self::createMailMock($testCase),
            SessionInterface::class => self::createSessionMock($testCase),
            CookieManager::class => self::createCookieManagerMock($testCase),
        ];

        $mocks[AuthService::class] = new AuthService(
            $mocks[Auth::class],
            $mocks[Mail::class],
            $mocks[SessionInterface::class],
            $mocks[CookieManager::class]
        );
        return $mocks;
    }
}
