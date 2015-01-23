<?php


namespace SensioLabs\JobBoardBundle\Tests\Controller;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;
use SensioLabs\JobBoardBundle\DataFixtures\ORM\LoadJobData;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseControllerTest extends WebTestCase
{

    public static function setUpBeforeClass() {

        parent::setUpBeforeClass();

        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }


    public function setUp() {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        $loader = new Loader();
        $loader->addFixture(new LoadJobData());

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($loader->getFixtures());

        parent::setUp();
    }

    public function testPostAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(10, $crawler->filter('#job-container > .box')->count());
    }
}
