<?php

namespace SensioLabs\JobBoardBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'attr' => array(
                    'placeholder' => 'entity.company.name',
                    'class' => 'company-name'
                ),
            ))
            ->add('country', 'country', array(
                'attr' => array(
                    'class' => 'company-country'
                ),
            ))
            ->add('city', 'text', array(
                'attr' => array(
                    'placeholder' => 'entity.company.city',
                    'class'       => 'location company-city',
                ),
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SensioLabs\JobBoardBundle\Entity\Company',
        ));
    }

    public function getName()
    {
        return 'company';
    }
}
