<?php


namespace SensioLabs\JobBoardBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;
use SensioLabs\Connect\Security\Authentication\Token\ConnectToken;
use SensioLabs\JobBoardBundle\DataFixtures\ORM\LoadJobData;
use SensioLabs\JobBoardBundle\DataFixtures\ORM\LoadUserData;
use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseControllerTest extends WebTestCase
{

    public static function setUpBeforeClass()
    {

        parent::setUpBeforeClass();

        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }


    public function setUp()
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $loader = new Loader();
        $loader->addFixture(new LoadUserData());
        $loader->addFixture(new LoadJobData());

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($loader->getFixtures());

        parent::setUp();
    }

    public function testIndexAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
        $this->assertEquals(Job::LIST_MAX_JOB_ITEMS, $crawler->filter('#job-container > .box')->count());
    }

    public function testXMLHttpRequestIndexAction()
    {
        $client = static::createClient();

        $ajaxCrawler = $client->request('GET', '/', array('page' => 2), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest',));
        $this->assertEquals(0, $ajaxCrawler->filter('#job-container')->count());
        $this->assertEquals(Job::LIST_MAX_JOB_ITEMS, $ajaxCrawler->filter('body > .box')->count());
    }

    public function testManageAction()
    {
        $client = static::createClient();

        //Not authenticated => Redirect to connect.sensiolabs
        $client->request('GET', '/manage');
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection());
        $this->assertStringStartsWith('https://connect.sensiolabs.com/', $response->headers->get('Location'));


        //Authenticated
        $this->logIn($client);

        $crawler = $client->request('GET', '/manage');
        $response = $client->getResponse();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(Job::LIST_ADMIN_MAX_JOB_ITEMS, $crawler->filter('#content > .box table > tbody > tr')->count());
    }

    private function logIn(Client $client)
    {
        $firewall = 'secured_area';

        $user = $client->getContainer()->get('sensiolabs_jobboard.security.userprovider')->loadUserByUsername('a8a1c2f5-995d-41a1-b0ae-fd9a1157bef6');
        $this->assertNotNull($user);

        $token = new ConnectToken($user, 'xxx', null, $firewall, null, $user->getRoles());

        $client->getContainer()->get('security.token_storage')->setToken($token);

        $session = $client->getContainer()->get('session');
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        return $client;
    }
}
