<?php

namespace SensioLabs\JobBoardBundle\Security\Authorization\Voter;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ApiAccessVoter implements VoterInterface
{
    const ROLE_API_ACCESS = 'API_ACCESS';

    protected $requestStack;
    protected $kernel;
    protected $apiAllowedHosts;

    public function __construct(RequestStack $requestStack, Kernel $kernel, $apiAllowedHosts = array())
    {
        $this->requestStack = $requestStack;
        $this->kernel = $kernel;
        $this->apiAllowedHosts = $apiAllowedHosts;
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

        $request = $this->requestStack->getCurrentRequest();

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            if (in_array($request->getHost(), $this->apiAllowedHosts)) {
                return VoterInterface::ACCESS_GRANTED;
            }

            if (in_array($this->kernel->getEnvironment(), array('test', 'dev'))) {
                return VoterInterface::ACCESS_GRANTED;
            }

            $result = VoterInterface::ACCESS_DENIED;
        }

        return $result;
    }

}
