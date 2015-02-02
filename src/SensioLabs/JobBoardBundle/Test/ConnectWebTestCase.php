<?php

namespace SensioLabs\JobBoardBundle\Test;

use Doctrine\ORM\EntityManager;
use SensioLabs\JobBoardBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;
use SensioLabs\JobBoardBundle\DataFixtures\ORM\LoadJobData;
use SensioLabs\JobBoardBundle\DataFixtures\ORM\LoadUserData;
use SensioLabs\Connect\Security\Authentication\Token\ConnectToken;
use Symfony\Component\HttpFoundation\Response;

abstract class ConnectWebTestCase extends WebTestCase
{
    public static $testUserUuid = 'a8a1c2f5-995d-41a1-b0ae-fd9a1157bef6';

    /** @var User */
    protected $user;

    /** @var Client */
    protected $client;

    /** @var EntityManager */
    protected $em;

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
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();

        $loader = new Loader();
        $loader->addFixture(new LoadUserData());
        $loader->addFixture(new LoadJobData());

        $purger = new ORMPurger($this->em);
        $executor = new ORMExecutor($this->em, $purger);
        $executor->execute($loader->getFixtures());

        /** @var User $user */
        $this->user = $this->client->getContainer()->get('sensiolabs_jobboard.security.userprovider')->loadUserByUsername(self::$testUserUuid);
        $this->assertNotNull($this->user);

        parent::setUp();
    }

    protected function logIn()
    {
        $firewall = 'secured_area';

        $token = new ConnectToken($this->user, 'xxx', null, $firewall, null, $this->user->getRoles());

        $this->client->getContainer()->get('security.token_storage')->setToken($token);

        $session = $this->client->getContainer()->get('session');
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();
    }

    protected function assertConnectRedirect(Response $response)
    {
        $this->assertTrue($response->isRedirect());
        $this->assertStringStartsWith('https://connect.sensiolabs.com/', $response->headers->get('Location'));
    }
}
