<?php

namespace SensioLabs\JobBoardBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use SensioLabs\JobBoardBundle\Entity\Company;
use SensioLabs\JobBoardBundle\Entity\Job;

class JobCompanySubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'preFlush',
        );
    }

    /**
     * @param Job $job
     * @param PreFlushEventArgs $args
     */
    public function preFlush(Job $job, PreFlushEventArgs $args)
    {
        /** @var EntityManager $em */
        $em = $args->getEntityManager();
        $actualCompany = $job->getCompany();
        $em->detach($actualCompany);

        $repository = $em->getRepository('SensioLabsJobBoardBundle:Company');
        $existingCompany = $repository->getUnique($actualCompany->getName(), $actualCompany->getCountry(), $actualCompany->getCity());

        if (null !== $existingCompany) {
            $job->setCompany($existingCompany);
        } else {
            $newCompany = clone $actualCompany;
            $job->setCompany($newCompany);
        }
    }
}
