<?php

namespace Document;

use App\Http\Documents\Document;
use Tests\Functional\WebTestCase;

class DocumentTest extends WebTestCase
{
    private Document $mockDocument;

    public function testGetAllFailed_DocumentNotFound()
    {
        $this->markTestSkipped('Временно отключен для рефакторинга');
        $response = $this->app()->handle(self::json('GET', '/api/documents/all'));

        self::assertEquals(404, $response->getStatusCode());
    }
}
