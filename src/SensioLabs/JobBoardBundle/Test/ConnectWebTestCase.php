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

        parent::setUp();
    }

    protected function getUser($userUuid)
    {
        /** @var User $user */
        $user = $this->client->getContainer()->get('sensiolabs_jobboard.security.userprovider')->loadUserByUsername($userUuid);
        $this->assertNotNull($user);

        return $user;
    }

    protected function getAdminUser()
    {
        return $this->getUser(LoadUserData::TEST_UUID_ADMIN);
    }

    protected function getSimpleUser()
    {
        return $this->getUser(LoadUserData::TEST_UUID_USER);
    }

    protected function logInAs(User $user)
    {
        $firewall = 'secured_area';

        $token = new ConnectToken($user, 'xxx', null, $firewall, null, $user->getRoles());

        $this->client->getContainer()->get('security.token_storage')->setToken($token);

        $session = $this->client->getContainer()->get('session');
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        return $this->client;
    }

    protected function logInAsAdmin()
    {
        $this->logInAs($this->getAdminUser());
    }

    protected function logInAsUser()
    {
        $this->logInAs($this->getSimpleUser());
    }

    protected function assertConnectRedirect(Response $response)
    {
        $this->assertTrue($response->isRedirect());
        $this->assertStringStartsWith('https://connect.sensiolabs.com/', $response->headers->get('Location'));
    }
}
