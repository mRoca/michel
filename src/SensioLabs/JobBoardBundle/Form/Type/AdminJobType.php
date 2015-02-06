<?php

namespace SensioLabs\JobBoardBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class AdminJobType extends JobType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('publishStart', 'date', array(
                'invalid_message' => 'job.publish_start.date',
                'widget'          => 'single_text',
                'format'          => 'MM/dd/yyyy',
                'attr'            => array(
                    'class'       => 'span3 datepicker',
                )
            ))
            ->add('publishEnd', 'date', array(
                'invalid_message' => 'job.publish_end.date',
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
