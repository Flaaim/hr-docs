<?php

namespace Tests\Unit\Mail;

use App\Http\Interface\MailSenderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class MockFactory
{
    public static function createMailSenderMock(TestCase $testCase): MockObject
    {
        return $testCase->getMockBuilder(MailSenderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public static function createTwigEnvironmentMock(TestCase $testCase): MockObject
    {
        return $testCase->getMockBuilder(Environment::class)->disableOriginalConstructor()
            ->getMock();
    }

    public static function create(TestCase $testCase): array
    {
        return [
            'sender' => self::createMailSenderMock($testCase),
            'twig' => self::createTwigEnvironmentMock($testCase),
        ];
    }
}
