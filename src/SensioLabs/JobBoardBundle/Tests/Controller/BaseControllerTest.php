<?php


namespace SensioLabs\JobBoardBundle\Tests\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Test\ConnectWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BaseControllerTest extends ConnectWebTestCase
{
    public function testIndexAction()
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertCount(Job::LIST_MAX_JOB_ITEMS, $crawler->filter('#job-container > .box'));
    }

    public function testXMLHttpRequestIndexAction()
    {
        $ajaxCrawler = $this->client->request('GET', '/', array('page' => 2), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest',));
        $this->assertCount(0, $ajaxCrawler->filter('#job-container'));
        $this->assertCount(Job::LIST_MAX_JOB_ITEMS, $ajaxCrawler->filter('body > .box'));
    }

    public function testManageAction()
    {
        // Not authenticated => Redirect to connect.sensiolabs
        $this->client->request('GET', '/manage');
        $this->assertConnectRedirect($this->client->getResponse());

        // Authenticated
        $this->logInAsUser();

        $crawler = $this->client->request('GET', '/manage');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertCount(Job::LIST_ADMIN_MAX_JOB_ITEMS, $crawler->filter('#content > .box table > tbody > tr'));
    }
}
