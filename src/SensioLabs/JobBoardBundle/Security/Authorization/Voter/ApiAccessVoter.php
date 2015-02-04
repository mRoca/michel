<?php

namespace SensioLabs\JobBoardBundle\Security\Authorization\Voter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ApiAccessVoter implements VoterInterface
{
    const ROLE_API_ACCESS = 'API_ACCESS';

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function supportsAttribute($attribute)
    {
        return $attribute === self::ROLE_API_ACCESS;
    }

    public function supportsClass($class)
    {
        return true;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        $request = $this->container->get('request');
        $allowedHosts = $this->container->getParameter('api_allowed_hosts');

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            if (in_array($request->getHost(), $allowedHosts)) {
                return VoterInterface::ACCESS_GRANTED;
            }

            if (in_array($this->container->get('kernel')->getEnvironment(), array('test', 'dev'))) {
                return VoterInterface::ACCESS_GRANTED;
            }

            $result = VoterInterface::ACCESS_DENIED;
        }

        return $result;
    }

}
