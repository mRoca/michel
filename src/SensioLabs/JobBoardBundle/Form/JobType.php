<?php


namespace SensioLabs\JobBoardBundle\Form;


use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JobType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array('attr' => array('placeholder' => 'Job title')))
            ->add('company', 'text', array('attr' => array('placeholder' => 'Company')))
            ->add('country', 'country')
            ->add('city', 'text', array('attr' => array('placeholder' => 'City')))
            ->add('contract', 'choice', array('choices' => Job::$CONTRACT_TYPES, 'empty_value' => 'Type of contract'))
            ->add('description', 'textarea')
            ->add('howToApply', 'text', array('attr' => array('placeholder' => 'Send your resume at...'), 'required' => false));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'SensioLabs\JobBoardBundle\Entity\Job'
            )
        );
    }


    public function getName()
    {
        return 'job';
    }
}
