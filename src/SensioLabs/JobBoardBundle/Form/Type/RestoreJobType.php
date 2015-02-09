<?php

namespace SensioLabs\JobBoardBundle\Form\Type;

use SensioLabs\JobBoardBundle\Validator\Constraints\JobCanBeRestored;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RestoreJobType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('restore', 'submit', array('label' => 'buttons.restore',));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'  => 'SensioLabs\JobBoardBundle\Entity\Job',
                'constraints' => new JobCanBeRestored()
            )
        );
    }

    public function getName()
    {
        return 'job_restore';
    }
}
