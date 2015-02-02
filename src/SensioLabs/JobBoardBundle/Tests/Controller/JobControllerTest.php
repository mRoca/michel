<?php


namespace SensioLabs\JobBoardBundle\Tests\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Test\ConnectWebTestCase;

class JobControllerTest extends ConnectWebTestCase
{

    public function testPostAction()
    {
        $crawler = $this->client->request('GET', '/post');

        $this->assertGreaterThan(0, $crawler->filter('html:contains("Post a job")')->count());
    }

    public function testPostActionSubmitError()
    {
        $crawler = $this->client->request('GET', '/post');
        $form = $crawler->selectButton('Preview')->form();

        $crawler = $this->client->submit($form, array());

        $this->assertGreaterThan(0, $crawler->filter('.error-container > ul > li')->count());
    }

    public function testPostActionSubmitSuccess()
    {
        $crawler = $this->client->request('GET', '/post');
        $form = $crawler->selectButton('Preview')->form();

        $this->client->submit($form, $this->jobData());

        $this->assertTrue($this->client->getResponse()->isRedirect('/FR/' . Job::CONTRACT_TYPE_FULL_TIME . '/my-job/preview'));
    }

    public function testPreviewAction()
    {
        $crawler = $this->client->request('GET', '/post');
        $form = $crawler->selectButton('Preview')->form();

        $this->client->submit($form, $this->jobData());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('body.preview'));
    }

    public function testUpdateAction()
    {
        $job = $this->em->getRepository('SensioLabsJobBoardBundle:Job')->findOneBy(array('user' => $this->user));
        $this->assertNotNull($job);

        $route = $this->client->getContainer()->get('router')->generate('job_update', $job->getUrlParameters(), false);

        // Not authenticated => Redirect to connect.sensiolabs
        $this->client->request('GET', $route);
        $this->assertConnectRedirect($this->client->getResponse());

        // Authenticated
        $this->logIn($this->client);

        $crawler = $this->client->request('GET', $route);
        $this->assertCount(1, $crawler->filter('body.add'));

        $form = $crawler->selectButton('Save')->form();

        $this->assertEquals($job->getTitle(), $form->get('job[title]')->getValue());

        $this->client->submit($form, $this->jobData());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('body.preview'));
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
