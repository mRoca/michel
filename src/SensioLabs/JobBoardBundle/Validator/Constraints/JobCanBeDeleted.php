<?php

namespace SensioLabs\JobBoardBundle\Validator\Constraints;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\Validator\Constraint;

class JobCanBeDeleted extends Constraint
{
    public $messages = array(
        Job::STATUS_PUBLISHED => 'job.delete.constraint.published',
        Job::STATUS_ARCHIVED=> 'job.delete.constraint.archived',
    );

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
