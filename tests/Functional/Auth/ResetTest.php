<?php

namespace Tests\Functional\Auth;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Tests\Functional\WebTestCase;

class ResetTest extends WebTestCase
{
    private Connection $database;
    public function setUp(): void
    {
        parent::setUp();
        $this->database = $this->app()->getContainer()->get('Doctrine\DBAL\Connection');
        //$this->testDbConnection();
        $this->database->beginTransaction();
    }
    public function testRequestPasswordReset()
    {
        $response = $this->app()->handle(self::json('POST', '/api/auth/requestReset'));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function tearDown(): void
    {
        $this->database->rollBack();
    }
}
