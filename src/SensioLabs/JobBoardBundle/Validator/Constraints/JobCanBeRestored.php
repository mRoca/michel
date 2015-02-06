<?php

namespace SensioLabs\JobBoardBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class JobCanBeRestored extends Constraint
{
    public $message = 'job.restore.constraint';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
