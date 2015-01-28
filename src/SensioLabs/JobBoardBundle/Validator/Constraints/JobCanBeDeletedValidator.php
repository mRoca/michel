<?php

namespace SensioLabs\JobBoardBundle\Validator\Constraints;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class JobCanBeDeletedValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint)
    {
        if ($object->getStatus() === Job::STATUS_PUBLISHED) {

            $this->context->buildViolation($constraint->message)
                ->setParameter('%job.title%', $object->getTitle())
                ->addViolation();

        }
    }
}
