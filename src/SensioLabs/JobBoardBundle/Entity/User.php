<?php


namespace SensioLabs\JobBoardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use SensioLabs\Connect\Api\Entity\User as ConnectApiUser;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="sl_user")
 * @ORM\Entity(repositoryClass="SensioLabs\JobBoardBundle\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\Column(type="string", length=255) */
    protected $uuid;

    /** @ORM\Column(type="string", length=255) */
    protected $username;

    /** @ORM\Column(type="string", length=255) */
    protected $name;

    /**
     * @var ArrayCollection|Job[]
     * @ORM\OneToMany(targetEntity="Job", mappedBy="user", fetch="EXTRA_LAZY")
     */
    protected $jobs;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
    }

    public function updateFromConnect(ConnectApiUser $apiUser)
    {
        $this->username = $apiUser->getUsername();
        $this->name = $apiUser->getName();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function getPassword()
    {
    }

    public function getSalt()
    {
    }

    public function eraseCredentials()
    {
    }

    /**
     * @return ArrayCollection|Job[]
     */
    public function getJobs()
    {
        return $this->jobs;
    }
}
