<?php


namespace SensioLabs\JobBoardBundle\Tests\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JobControllerTest extends WebTestCase
{

    public function testPostAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/post');

        $this->assertGreaterThan(0, $crawler->filter('html:contains("Post a job")')->count());
    }

    public function testPostActionSubmitError()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/post');
        $form = $crawler->selectButton('Preview')->form();

        $crawler = $client->submit($form, array());

        $this->assertGreaterThan(0, $crawler->filter('.error-container > ul > li')->count());
    }

    public function testPostActionSubmitSuccess()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/post');
        $form = $crawler->selectButton('Preview')->form();

        $client->submit($form, $this->jobData());

        $this->assertTrue($client->getResponse()->isRedirect('/FR/' . Job::CONTRACT_TYPE_FULL_TIME . '/my-job/preview'));
    }

    public function testPreviewAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/post');
        $form = $crawler->selectButton('Preview')->form();

        $client->submit($form, $this->jobData());
        $crawler = $client->followRedirect();

        $this->assertEquals(1, $crawler->filter('body.preview')->count());
    }

    protected function jobData()
    {
        return array(
            'job[title]'       => 'My job',
            'job[company]'     => 'My company',
            'job[country]'     => 'FR',
            'job[city]'        => 'PARIS',
            'job[contract]'    => Job::CONTRACT_TYPE_FULL_TIME,
            'job[description]' => '<b>My</b> Description',
        );
    }
}
