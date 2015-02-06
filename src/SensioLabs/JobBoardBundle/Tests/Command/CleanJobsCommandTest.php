<?php

namespace SensioLabs\JobBoardBundle\Tests\Command;

use SensioLabs\JobBoardBundle\Command\CleanJobsCommand;
use SensioLabs\JobBoardBundle\Test\ConnectWebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CleanJobsCommandTest extends ConnectWebTestCase
{
    public function testExecute()
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new CleanJobsCommand());

        $command = $application->find('jobboard:jobs:clean');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                '--days' => 0,
            )
        );

        $this->assertContains('1 deleted jobs cleaned.', $commandTester->getDisplay());
    }
}
