<?php

namespace SensioLabs\JobBoardBundle\EventListener\Job;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use SensioLabs\JobBoardBundle\Entity\Job;

class JobEntitySubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
            'preFlush',
        );
    }

    /**
     * @param Job $job
     * @param LifecycleEventArgs|PreUpdateEventArgs $args
     */
    public function prePersist(Job $job, LifecycleEventArgs $args)
    {
        if ($job->isDeleted()) {
            $job->setDeletedAt(new \DateTime());
        }
    }

    /**
     * @param Job $job
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(Job $job, PreUpdateEventArgs $args)
    {
        if (!$args->hasChangedField('status')) {
            return;
        }

        if ($args->getNewValue('status') === Job::STATUS_DELETED) {
            $job->setDeletedAt(new \DateTime());
        } elseif ($args->getOldValue('status') === Job::STATUS_DELETED) {
            $job->setDeletedAt(null);
        }
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
