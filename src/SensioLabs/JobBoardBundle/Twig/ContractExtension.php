<?php


namespace SensioLabs\JobBoardBundle\Twig;

use SensioLabs\JobBoardBundle\Entity\Job;

class ContractExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('contractName', array($this, 'contractName')),
        );
    }

    public function contractName($contractCode)
    {
        return Job::getContractName($contractCode);
    }

    public function getName()
    {
        return 'contract_extension';
    }
}
