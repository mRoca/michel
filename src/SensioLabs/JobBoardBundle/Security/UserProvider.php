<?php

namespace SensioLabs\JobBoardBundle\Security;

use SensioLabs\JobBoardBundle\Entity\User;
use SensioLabs\JobBoardBundle\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param string $uuid
     * @return User
     */
    public function loadUserByUsername($uuid)
    {
        $user = $this->userRepository->findOneByUuid($uuid);

        if (!$user) {
            $user = new User($uuid);
        }

        return $user;
    }

    /**
     * @param UserInterface $user
     * @return User
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('class %s is not supported', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUuid());
    }

    public function supportsClass($class)
    {
        return 'SensioLabs\JobBoardBundle\Entity\User' === $class;
    }
}
