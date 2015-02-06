<?php

namespace SensioLabs\JobBoardBundle\Filter\Form\Type;

use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JobFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('country', 'filter_choice', array(
            'choices'      => Intl::getRegionBundle()->getCountryNames(),
            'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                if (!empty($values['value'])) {
                    $qb = $filterQuery->getQueryBuilder();
                    $qb->andWhere($qb->expr()->eq("company.country", ':country'))
                        ->setParameter('country', $values['value']);
                }
            }
        ));

        $builder->add('contract', 'choice', array('choices' => Job::$CONTRACT_TYPES));
    }

    public function getName()
    {
        return 'job_filter';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
            'validation_groups' => array('filtering') // avoid NotBlank() constraint-related message
        ));
    }
}
