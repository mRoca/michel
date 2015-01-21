<?php


namespace SensioLabs\JobBoardBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Job;

class LoadJobData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {

        for ($i = 0; $i < 50; $i++) {
            $job = new Job();
            $job
                ->setTitle('My great job ' . ($i + 1))
                ->setCountry('FR')
                ->setCity(rand(0, 1) ? 'Clichy' : 'Lille')
                ->setCompany(rand(0, 1) ? 'SensioLabs' : 'Extrême Sensio')
                ->setContract(Job::getContractTypesKeys()[rand(0, count(Job::getContractTypesKeys()) - 1)])
                ->setDescription('<p><b>Lorem ipsum</b> dolor sit amet, consectetur adipiscing elit. In quis nulla augue. Phasellus enim eros, luctus a dapibus ut, auctor et urna. Donec eget egestas mi. Sed auctor semper elit, quis elementum orci pulvinar ut. Donec leo quam, elementum non vulputate ut, laoreet eu augue. Pellentesque tempus luctus porttitor. In id orci enim, quis gravida massa. Praesent consequat hendrerit consequat. Cras nulla elit, pulvinar a dapibus vitae, pellentesque...</p>')
                ->setHowToApply('Send your CV to : <a href="mailto:contact@nocompany.com">contact@nocompany.com</a>');

            $manager->persist($job);
        }

        $manager->flush();
    }
}
