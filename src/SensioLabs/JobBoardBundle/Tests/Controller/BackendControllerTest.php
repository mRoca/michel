<?php

namespace SensioLabs\JobBoardBundle\Tests\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Test\ConnectWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BackendControllerTest extends ConnectWebTestCase
{
    public function testListAction()
    {
        // Not authenticated => Redirect to connect.sensiolabs
        $this->client->request('GET', '/backend');
        $this->assertConnectRedirect($this->client->getResponse());

        // Authenticated as user => Forbidden
        $this->logInAsUser();
        $this->client->request('GET', '/backend');
        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        // Authenticated as admin
        $this->logInAsAdmin();
        $crawler = $this->client->request('GET', '/backend');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertCount(Job::LIST_ADMIN_MAX_JOB_ITEMS, $crawler->filter('#backend-job-container > table > tbody > tr'));

        // Table sorting test
        // Here we override the $_GET global : the knp-paginator "paginate" method use it...
        // Maybe there is another way ?
        $savedGet = $_GET;
        $_GET = array('sort' => 'job.title', 'direction' => 'desc');
        $crawler = $this->client->request('GET', '/backend');
        $this->assertSame('My great job 50', $crawler->filter('#backend-job-container > table > tbody > tr > td')->eq(1)->first()->text());
        $_GET = $savedGet;

        // Delete button
        $form = $crawler->filter('#backend-job-container > table > tbody > tr')->first()->selectButton('job_delete[delete]')->form();
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        // Published announcement => cannot delete
        $this->assertCount(1, $crawler->filter('#backend-job-container .error'));
    }

    public function testListArchivedAction()
    {
        // Not authenticated => Redirect to connect.sensiolabs
        $this->client->request('GET', '/backend', array('status' => 'archived'));
        $this->assertConnectRedirect($this->client->getResponse());

        // Authenticated as admin
        $this->logInAsAdmin();
        $crawler = $this->client->request('GET', '/backend', array('status' => 'archived'));
        $this->assertCount(10, $crawler->filter('#backend-job-container > table > tbody > tr'));

        // Delete button
        $form = $crawler->filter('#backend-job-container > table > tbody > tr')->first()->selectButton('job_delete[delete]')->form();
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        // Archived announcement => cannot delete
        $this->assertCount(1, $crawler->filter('#backend-job-container .error'));
    }

    public function testEditAction()
    {
        $job = $this->em->getRepository('SensioLabsJobBoardBundle:Job')->findOneBy(array('status' => Job::STATUS_PUBLISHED));
        $this->assertNotNull($job);
        $this->assertTrue($job->isPublished());

        $route = '/backend/' . $job->getId() . '/edit';

        // Not authenticated => Redirect to connect.sensiolabs
        $this->client->request('GET', $route);
        $this->assertConnectRedirect($this->client->getResponse());

        // Authenticated
        $this->logInAsAdmin();

        $crawler = $this->client->request('GET', $route);
        $this->assertCount(1, $crawler->filter('body.backend.add'));

        // The job is published, we untick the box and resubmit the form to unpublish it
        $form = $crawler->selectButton('save')->form();
        $form['adminjob[publish]']->untick();
        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect('/backend'));

        $this->em->clear();
        $job = $this->em->getRepository('SensioLabsJobBoardBundle:Job')->find($job->getId());
        $this->assertFalse($job->isPublished());
    }
}
