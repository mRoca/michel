<?php


namespace SensioLabs\JobBoardBundle\Tests\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Test\ConnectWebTestCase;
use Symfony\Component\HttpFoundation\Response;

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
        $job = $this->em->getRepository('SensioLabsJobBoardBundle:Job')->findOneBy(array('user' => $this->getSimpleUser(), 'status' => Job::STATUS_PUBLISHED));
        $this->assertNotNull($job);

        $route = $this->client->getContainer()->get('router')->generate('job_update', $job->getUrlParameters(), false);

        // Not authenticated => Redirect to connect.sensiolabs
        $this->client->request('GET', $route);
        $this->assertConnectRedirect($this->client->getResponse());

        // Authenticated
        $this->logInAsUser();

        $crawler = $this->client->request('GET', $route);
        $this->assertCount(1, $crawler->filter('body.add'));

        $form = $crawler->selectButton('save')->form();

        $this->assertSame($job->getTitle(), $form->get('job[title]')->getValue());

        $this->client->submit($form, $this->jobData());
        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $crawler->filter('body.preview'));
    }

    public function testShowAction()
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertGreaterThan(0, $crawler->filter('#job-container > .box a.title')->count());
        $link = $crawler->filter('#job-container > .box a.title')->first()->link();

        $this->client->click($link);
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testViewsCount()
    {
        $jobRepository = $this->em->getRepository('SensioLabsJobBoardBundle:Job');

        // API views count
        $this->client->request('GET', '/api/random');
        $this->assertJsonResponse($this->client->getResponse());
        $result = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $result);

        $this->em->clear();
        $job = $jobRepository->find($result['id']);
        $this->assertSame(1, $job->getApiViewsCount());

        // List views count
        $this->em->clear();
        $initialJob = $jobRepository->getListQb(Job::STATUS_PUBLISHED)->setMaxResults(1)->getQuery()->getSingleResult();
        $this->assertNotNull($initialJob);

        $this->client->request('GET', '/');
        $this->em->clear();
        $job = $jobRepository->find($initialJob->getId());
        $this->assertEquals($initialJob->getListViewsCount() + 1, $job->getListViewsCount());

        // Display views count
        $route = $this->client->getContainer()->get('router')->generate('job_show', $job->getUrlParameters(), false);
        $this->client->request('GET', $route);
        $this->em->clear();
        $job = $jobRepository->find($initialJob->getId());
        $this->assertEquals($initialJob->getDisplayViewsCount() + 1, $job->getDisplayViewsCount());
    }

    public function testChangeStatusAction()
    {
        $jobRepository = $this->em->getRepository('SensioLabsJobBoardBundle:Job');
        $job = $jobRepository->findOneBy(array('user' => $this->getSimpleUser(), 'status' => Job::STATUS_PUBLISHED));
        $this->assertNotNull($job);

        $route = $this->client->getContainer()->get('router')->generate(
            'job_udpate_status',
            array_merge($job->getUrlParameters(), array('status' => Job::STATUS_ARCHIVED)),
            false
        );

        // Not authenticated => Redirect to connect.sensiolabs
        $this->client->request('GET', $route);
        $this->assertConnectRedirect($this->client->getResponse());

        // Authenticated
        $this->logInAsUser();

        $this->client->request('GET', $route);
        $this->em->clear();
        $job = $jobRepository->find($job->getId());
        $this->assertSame(Job::STATUS_ARCHIVED, $job->getStatus());
    }

    protected function jobData()
    {
        return array(
            'job[title]'            => 'My job',
            'job[company][name]'    => 'My company',
            'job[company][country]' => 'FR',
            'job[company][city]'    => 'PARIS',
            'job[contract]'         => Job::CONTRACT_TYPE_FULL_TIME,
            'job[description]'      => '<b>My</b> Description',
        );
    }
}
