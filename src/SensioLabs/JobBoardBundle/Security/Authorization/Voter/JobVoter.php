<?php

namespace SensioLabs\JobBoardBundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\User\UserInterface;

class JobVoter extends AbstractVoter
{
    const EDIT = 'edit';

    protected function getSupportedAttributes()
    {
        return array(self::EDIT);
    }

    protected function getSupportedClasses()
    {
        return array('SensioLabs\JobBoardBundle\Entity\Job');
    }

    protected function isGranted($attribute, $job, $user = null)
    {
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($user === $job->getUser()) {
            return true;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        return false;
    }
}
