<?php

namespace SensioLabs\JobBoardBundle\Form\Type;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\Form\FormBuilderInterface;

class AdminJobType extends JobType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('publishStart', 'date', array(
                'invalid_message' => 'The "from" date is invalid',
                'widget'          => 'single_text',
                'format'          => 'MM/dd/yyyy',
                'attr'            => array(
                    'class'       => 'span3 datepicker',
                )
            ))
            ->add('publishEnd', 'date', array(
                'invalid_message' => 'The "to" date is invalid',
                'widget'          => 'single_text',
                'format'          => 'MM/dd/yyyy',
                'attr'            => array(
                    'class'       => 'span3 datepicker',
                )
            ))
            ->add('publish', 'checkbox', array(
                'mapped' => false,
                'data'   => $builder->getData()->isPublished()
            ));
    }

    public function getName()
    {
        return 'adminjob';
    }
}
