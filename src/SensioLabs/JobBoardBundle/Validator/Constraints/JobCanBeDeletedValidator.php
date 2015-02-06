<?php

namespace SensioLabs\JobBoardBundle\Validator\Constraints;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class JobCanBeDeletedValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint)
    {
        if (in_array($object->getStatus(), array(Job::STATUS_PUBLISHED, Job::STATUS_ARCHIVED))) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%job.title%', $object->getTitle())
                ->setParameter('%job.status%', strtolower($object->getStatus()))
                ->addViolation();
        }
    }
}
