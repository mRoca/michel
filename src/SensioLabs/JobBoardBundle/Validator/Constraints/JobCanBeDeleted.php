<?php

namespace SensioLabs\JobBoardBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class JobCanBeDeleted extends Constraint
{
    public $message = 'You cannot delete "%job.title%", it must not be published';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
