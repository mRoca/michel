<?php


namespace SensioLabs\JobBoardBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SensioLabs\JobBoardBundle\Entity\Company;
use SensioLabs\JobBoardBundle\Entity\Job;

class LoadJobData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $sensiolabs = new Company();
        $sensiolabs
            ->setName('SensioLabs')
            ->setCity('Lille')
            ->setCountry('FR');
        $manager->persist($sensiolabs);

        $sensiolabsUK = new Company();
        $sensiolabsUK
            ->setName('SensioLabs UK')
            ->setCity('London')
            ->setCountry('GB');
        $manager->persist($sensiolabsUK);
        $manager->flush();

        for ($i = 0; $i < 50; $i++) {
            $job = new Job();
            $job
                ->setStatus(Job::STATUS_PUBLISHED)
                ->setUser($this->getReference('user'))
                ->setTitle('My great job ' . ($i + 1))
                ->setCompany($i % 2 ? $sensiolabs : $sensiolabsUK)
                ->setContract(Job::getContractTypesKeys()[rand(0, count(Job::getContractTypesKeys()) - 1)])
                ->setDescription('<p><b>Lorem ipsum</b> dolor sit amet, consectetur adipiscing elit. In quis nulla augue. Phasellus enim eros, luctus a dapibus ut, auctor et urna. Donec eget egestas mi. Sed auctor semper elit, quis elementum orci pulvinar ut. Donec leo quam, elementum non vulputate ut, laoreet eu augue. Pellentesque tempus luctus porttitor. In id orci enim, quis gravida massa. Praesent consequat hendrerit consequat. Cras nulla elit, pulvinar a dapibus vitae, pellentesque...</p>')
                ->setHowToApply('Send your CV to : <a href="mailto:contact@nocompany.com">contact@nocompany.com</a>');

            if ($i < 10) {
                $job->setStatus(Job::STATUS_ARCHIVED);
            }

            if ($i < 1) {
                $job->setStatus(Job::STATUS_DELETED);
            }

            if ($job->isPublished() && ($i % 2)) {
                $job->setPublishStart(new \DateTime("last week - " . rand(1, 90) . " days"));
                $job->setPublishEnd(new \DateTime("next week +" . rand(0, 90) . " days"));
            }

            $manager->persist($job);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
