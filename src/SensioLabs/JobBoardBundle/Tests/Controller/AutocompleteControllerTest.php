<?php

namespace SensioLabs\JobBoardBundle\Tests\Controller;

use SensioLabs\JobBoardBundle\Test\ConnectWebTestCase;

class AutocompleteControllerTest extends ConnectWebTestCase
{
    public function testCityAction()
    {
        $this->client->request('GET', '/autocomplete/city?country=FR&search=lil');
        $this->assertJsonResponse($this->client->getResponse());
        $result = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $result);
        $this->assertContains('Lille', $result);

        $this->client->request('GET', '/autocomplete/city?country=UK&search=lil');
        $this->assertJsonResponse($this->client->getResponse());
        $result = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(0, $result);
    }

    public function testCompanyAction()
    {
        $this->client->request('GET', '/autocomplete/company?country=FR&city=Lille&search=sen');
        $this->assertJsonResponse($this->client->getResponse());
        $result = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $result);
        $this->assertContains('SensioLabs', $result);
    }

}
