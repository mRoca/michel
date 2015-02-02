<?php

namespace SensioLabs\JobBoardBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class JobCanBeRestored extends Constraint
{
    public $message = 'You cannot restore "%job.title%", it must be deleted';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
