<?php

namespace SensioLabs\JobBoardBundle\Form\Type;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JobType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'attr' => array(
                    'placeholder' => 'entity.job.title',
                    'class'       => 'title-input',
                ),
            ))
            ->add('company', 'company')
            ->add('contract', 'choice', array(
                'choices'     => Job::$CONTRACT_TYPES,
                'empty_value' => 'entity.job.contract_field'
            ))
            ->add('description', 'textarea', array(
                'attr' => array('class' => 'ckeditor')
            ))
            ->add('howToApply', 'text', array(
                'attr'     => array('placeholder' => 'form.label.job.how_to_apply'),
                'required' => false
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SensioLabs\JobBoardBundle\Entity\Job',
        ));
    }

    public function getName()
    {
        return 'job';
    }
}
