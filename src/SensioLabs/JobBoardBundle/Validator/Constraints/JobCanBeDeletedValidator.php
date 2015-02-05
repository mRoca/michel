<?php

namespace SensioLabs\JobBoardBundle\Validator\Constraints;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class JobCanBeDeletedValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint)
    {
        if (isset($constraint->messages[$object->getStatus()])){
            $this->context->buildViolation($constraint->messages[$object->getStatus()])
                ->setParameter('%job.title%', $object->getTitle())
                ->addViolation();
        }
    }
}
