<?php

namespace SensioLabs\JobBoardBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class JobCanBeRestoredValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint)
    {
        if (!$object->isDeleted()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%job.title%', $object->getTitle())
                ->addViolation();
        }
    }
}
