<?php

namespace Tests\Functional;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\Exception;

class HomeTest extends WebTestCase
{
    private Connection $database;
    public function setUp(): void
    {
        parent::setUp();

        $this->database = $this->app()->getContainer()->get('Doctrine\DBAL\Connection');
        $this->testDbConnection();
        $this->database->beginTransaction();
    }
    /**
     * @runInSeparateProcess
     */
    public function testSuccess()
    {

        $response = $this->app()->handle(self::html('GET', '/'));

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('text/html', $response->getHeaderLine('Content-Type'));
    }
    /**
     * @runInSeparateProcess
     */
    public function testDbConnection()
    {
        try {
            $result = $this->database->executeQuery('SELECT 1')->fetchOne();
            $this->assertEquals(1, $result, 'Database connection failed');
        } catch (\Exception $e) {
            $this->fail("DB connection error: " . $e->getMessage());
        }
    }
    public function tearDown(): void
    {
        $this->database->rollBack();
    }
}
