<?php

namespace SensioLabs\JobBoardBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use SensioLabs\JobBoardBundle\Form\EventListener\CompanyFieldSubscriber;
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
                ),
            ))
            ->add('country', 'country')
            ->add('city', 'text', array(
                'attr' => array(
                    'placeholder' => 'entity.company.city',
                    'class'       => 'location',
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
