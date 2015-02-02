<?php

namespace SensioLabs\JobBoardBundle\Services;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\HttpFoundation\Session\Session;

class PostedJobSessionHandler
{
    /** @var Session */
    protected $session;

    /** @var SerializerInterface */
    protected $serializer;

    const POSTED_JOB_SESSION_KEY = 'current_posted_job';
    const POSTED_JOB_SERIALIZER_GROUP = 'post_session';

    public function __construct(Session $session, SerializerInterface $serializer)
    {
        $this->session = $session;
        $this->serializer = $serializer;
    }

    /**
     * @param Job $job
     */
    public function setPostedJob(Job $job)
    {
        $serialized = $this->serializer->serialize(
            $job,
            'json',
            SerializationContext::create()->setGroups(array(self::POSTED_JOB_SERIALIZER_GROUP))
        );

        $this->session->set(self::POSTED_JOB_SESSION_KEY, $serialized);
    }

    /**
     * @return Job
     */
    public function getPostedJob()
    {
        $serialized = $this->session->get(self::POSTED_JOB_SESSION_KEY);

        if (null === $serialized) {
            return null;
        }

        return $this->serializer->deserialize($serialized, 'SensioLabs\JobBoardBundle\Entity\Job', 'json');
    }

    /**
     * @return bool
     */
    public function hasPostedJob()
    {
        return $this->session->has(self::POSTED_JOB_SESSION_KEY) && $this->getPostedJob() instanceof Job;
    }

    public function clearPostedJob()
    {
        $this->session->remove(self::POSTED_JOB_SESSION_KEY);
    }

}
