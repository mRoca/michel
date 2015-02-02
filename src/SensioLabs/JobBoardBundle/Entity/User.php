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

    /** @ORM\Column(type="string", length=255, nullable=true) */
    protected $email;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isAdmin;

    /**
     * @var ArrayCollection|Job[]
     * @ORM\OneToMany(targetEntity="Job", mappedBy="user", fetch="EXTRA_LAZY")
     */
    protected $jobs;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
        $this->isAdmin = false;
        $this->jobs = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getUsername();
    }

    public function updateFromConnect(ConnectApiUser $apiUser)
    {
        $this->username = $apiUser->getUsername();
        $this->name = $apiUser->getName();
        $this->email = $apiUser->getEmail();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = array('ROLE_USER');

        if (null !== $this->id) $roles[] = 'ROLE_CONNECT_USER';
        if ($this->isAdmin) $roles[] = 'ROLE_ADMIN';

        return $roles;
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

    /**
     * @return boolean
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * @param boolean $isAdmin
     * @return $this
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }
}
