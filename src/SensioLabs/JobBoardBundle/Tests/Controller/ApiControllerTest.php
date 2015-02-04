<?php

namespace SensioLabs\JobBoardBundle\Tests\Controller;

use SensioLabs\JobBoardBundle\Test\ConnectWebTestCase;

class ApiControllerTest extends ConnectWebTestCase
{
    public function testRandomAction()
    {
        $this->client->request('GET', '/api/random');
        $this->assertJsonResponse($this->client->getResponse());
        $result = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('title', $result);
    }
}
