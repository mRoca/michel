<?php


namespace SensioLabs\JobBoardBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SensioLabs\JobBoardBundle\Entity\User;

class UserRepository extends EntityRepository
{
    /**
     * @param string $uuid
     * @return User
     */
    public function findOneByUuid($uuid)
    {
        return $this->findOneBy(array('uuid' => $uuid));
    }
}
