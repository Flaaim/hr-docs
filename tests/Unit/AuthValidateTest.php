<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Respect\Validation\Validator;

class AuthValidateTest extends TestCase
{
    public function testEmailValidation(): void
    {
        $validEmails = ['test@example.com', 'user.name+tag@sub.domain.com'];
        $invalidEmails = ['test@', '@example.com', 'test@.com', ' test@example.com ', 'какой@аава.ru'];

        foreach ($validEmails as $email) {
            self::assertTrue(Validator::email()->validate($email));
        }

        foreach ($invalidEmails as $email) {
            self::assertFalse(Validator::email()->validate($email));
        }
    }

    public function testPasswordValidation(): void
    {
        $validPasswords = ['123456', 'asdqwe', 'tst121321'];
        $invalidPasswords = [' ', '@231', '!_#$'];
        foreach ($validPasswords as $password) {
            self::assertTrue(Validator::stringType()->length(6, 12)->validate($password));
        }

        foreach ($invalidPasswords as $password) {
            self::assertFalse(Validator::stringType()->length(6, 12)->validate($password));
        }
    }
}
