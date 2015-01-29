<?php

namespace SensioLabs\JobBoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Job
 *
 * @Entity(repositoryClass="SensioLabs\JobBoardBundle\Repository\JobRepository")
 *
 * @ORM\Table(name="jobs", indexes={
 *      @ORM\Index(name="country_idx", columns={"country"}),
 *      @ORM\Index(name="contract_idx", columns={"contract"}),
 *      @ORM\Index(name="slug_idx", columns={"slug"})
 * })
 *
 */
class Job
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="jobs")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Job title should not be empty")
     */
    protected $title;

    /**
     * @var string
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $slug;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Company should not be empty")
     */
    protected $company;

    /**
     * @var string
     * @ORM\Column(type="string", length=2)
     * @Assert\NotBlank(message="Country should not be empty")
     */
    protected $country;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="City should not be empty")
     */
    protected $city;

    /**
     * @var string
     * @ORM\Column(type="string", length=31)
     * @Assert\NotBlank(message="Contract must be selected")
     * @Assert\Choice(callback = "getContractTypesKeys", message="Contract must be selected")
     */
    protected $contract;

    /**
     * @var string
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Your job offer must be longer")
     */
    protected $description;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true, name="how_to_apply")
     */
    protected $howToApply;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true, name="publish_start")
     */
    protected $publishStart;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true, name="publish_end")
     */
    protected $publishEnd;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true, name="created_at")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true, name="updated_at")
     */
    protected $updatedAt;

    const LIST_MAX_JOB_ITEMS = 10;
    const LIST_ADMIN_MAX_JOB_ITEMS = 25;

    const CONTRACT_TYPE_FULL_TIME = 'CDI';
    const CONTRACT_TYPE_PART_TIME = 'PART_TIME';
    const CONTRACT_TYPE_INTERNSHIP = 'INTERNSHIP';
    const CONTRACT_TYPE_FREELANCE = 'FREELANCE';
    const CONTRACT_TYPE_ALTERNANCE = 'ALTERNANCE';

    public static $CONTRACT_TYPES = array(
        self::CONTRACT_TYPE_FULL_TIME  => "Full Time",
        self::CONTRACT_TYPE_PART_TIME  => "Part Time",
        self::CONTRACT_TYPE_INTERNSHIP => "Internship",
        self::CONTRACT_TYPE_FREELANCE  => "Freelance",
        self::CONTRACT_TYPE_ALTERNANCE => "Alternance"
    );

    /** @return string */
    public static function getContractName($contractCode)
    {
        return isset(self::$CONTRACT_TYPES[$contractCode]) ? self::$CONTRACT_TYPES[$contractCode] : '';
    }

    /** @return array */
    public static function getContractTypesKeys()
    {
        return array_keys(self::$CONTRACT_TYPES);
    }

    /**
     * Create an array used by the generateUrl method
     * @return array
     */
    public function getUrlParameters()
    {
        return array(
            'country'  => strtoupper($this->getCountry()),
            'contract' => strtoupper($this->getContract()),
            'slug'     => $this->getSlug() ? $this->getSlug() : Urlizer::urlize($this->getTitle()),
        );
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }


    /**
     * Set title
     *
     * @param string $title
     * @return Job
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return Job
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return Job
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Job
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set contract
     *
     * @param string $contract
     * @return Job
     */
    public function setContract($contract)
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * Get contract
     *
     * @return string
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Job
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set howToApply
     *
     * @param string $howToApply
     * @return Job
     */
    public function setHowToApply($howToApply)
    {
        $this->howToApply = $howToApply;

        return $this;
    }

    /**
     * Get howToApply
     *
     * @return string
     */
    public function getHowToApply()
    {
        return $this->howToApply;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPublishStart()
    {
        return $this->publishStart;
    }

    /**
     * @param \DateTime $publishStart
     * @return $this
     */
    public function setPublishStart($publishStart)
    {
        $this->publishStart = $publishStart;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPublishEnd()
    {
        return $this->publishEnd;
    }

    /**
     * @param \DateTime $publishEnd
     * @return $this
     */
    public function setPublishEnd($publishEnd)
    {
        $this->publishEnd = $publishEnd;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
