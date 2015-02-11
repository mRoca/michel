<?php

namespace SensioLabs\JobBoardBundle\Filter\Form\Type;

use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JobFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('country', 'choice', array('choices' => Intl::getRegionBundle()->getCountryNames()));
        $builder->add('contract', 'choice', array('choices' => Job::$CONTRACT_TYPES));
    }

    public function getName()
    {
        return 'job_filter';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }
}
