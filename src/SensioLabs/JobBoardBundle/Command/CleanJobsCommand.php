<?php

namespace SensioLabs\JobBoardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanJobsCommand extends ContainerAwareCommand
{
    const DELETING_DAYS = 20;

    protected function configure()
    {
        $this
            ->setName('jobboard:jobs:clean')
            ->setDescription('> Delete from the database all announcements having the status « deleted » since at least x days')
            ->addOption(
                'days',
                'd',
                InputOption::VALUE_OPTIONAL,
                'How many days ?',
                self::DELETING_DAYS
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $days = intval($input->getOption('days'));

        if (!($days >= 0)) {
            $output->writeln('<error>Invalid days parameter</error>');
            return;
        }

        $message = sprintf('Cleaning deleted jobs older than %d days', $days);
        $this->logInfo($message);
        $output->writeln($message);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('SensioLabsJobBoardBundle:Job');
        $cleaned = $repository->deleteOldJobs($days);

        $message = sprintf('%d deleted jobs cleaned.', $cleaned);
        $this->logInfo($message);
        $output->writeln("<info>$message</info>");
    }

    protected function logInfo($text)
    {
        $logger = $this->getContainer()->get('logger');
        $logger->info('[jobboard:jobs:clean] ' . $text);
    }
}
