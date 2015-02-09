<?php

namespace SensioLabs\JobBoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eko\FeedBundle\Item\Writer\RoutedItemInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Job
 *
 * @ORM\Entity(repositoryClass="SensioLabs\JobBoardBundle\Repository\JobRepository")
 *
 * @ORM\Table(name="jobs", indexes={
 *      @ORM\Index(name="contract_idx", columns={"contract"}),
 *      @ORM\Index(name="slug_idx", columns={"slug"}),
 *      @ORM\Index(name="title_idx", columns={"title"}),
 *      @ORM\Index(name="created_at_idx", columns={"created_at"}),
 *      @ORM\Index(name="status_idx", columns={"status"}),
 * })
 *
 * @ORM\EntityListeners({"SensioLabs\JobBoardBundle\EventListener\Job\JobEntitySubscriber"})
 */
class Job implements RoutedItemInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Groups({"api"})
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
     * @Assert\NotBlank(message="job.title.not_blank")
     * @JMS\Groups({"post_session", "api"})
     */
    protected $title;

    /**
     * @var string
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $slug;

    /**
     * @var Company
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="jobs", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Valid()
     * @JMS\Groups({"post_session"})
     */
    protected $company;

    /**
     * @var string
     * @ORM\Column(type="string", length=31)
     * @Assert\NotBlank(message="job.contract.not_blank")
     * @Assert\Choice(callback = "getContractTypesKeys", message="job.contract.callback")
     * @JMS\Groups({"post_session", "api"})
     */
    protected $contract;

    /**
     * @var string
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="job.description.not_blank")
     * @JMS\Groups({"post_session"})
     */
    protected $description;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true, name="how_to_apply")
     * @JMS\Groups({"post_session"})
     */
    protected $howToApply;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true, name="publish_start")
     * @Assert\Date()
     */
    protected $publishStart;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true, name="publish_end")
     * @Assert\Date()
     */
    protected $publishEnd;

    /**
     * @var string
     * @ORM\Column(type="string", length=31)
     * @Assert\Choice(callback="getStatusesKeys", message="job.status.callback")
     */
    protected $status;

    /**
     * @var int
     * @ORM\Column(type="integer", name="list_views_count")
     */
    protected $listViewsCount = 0;

    /**
     * @var int
     * @ORM\Column(type="integer", name="display_views_count")
     */
    protected $displayViewsCount = 0;

    /**
     * @var int
     * @ORM\Column(type="integer", name="api_views_count")
     */
    protected $apiViewsCount = 0;

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

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true, name="deleted_at")
     */
    protected $deletedAt;

    const LIST_MAX_JOB_ITEMS = 10;
    const LIST_ADMIN_MAX_JOB_ITEMS = 25;

    const STATUS_NEW = 'NEW';
    const STATUS_ORDERED = 'ORDERED';
    const STATUS_PUBLISHED = 'PUBLISHED';
    const STATUS_EXPIRED = 'EXPIRED';
    const STATUS_ARCHIVED = 'ARCHIVED';
    const STATUS_DELETED = 'DELETED';
    const STATUS_RESTORED = 'RESTORED';

    const CONTRACT_TYPE_FULL_TIME = 'CDI';
    const CONTRACT_TYPE_PART_TIME = 'PART_TIME';
    const CONTRACT_TYPE_INTERNSHIP = 'INTERNSHIP';
    const CONTRACT_TYPE_FREELANCE = 'FREELANCE';
    const CONTRACT_TYPE_ALTERNANCE = 'ALTERNANCE';

    public static $STATUSES = array(
        self::STATUS_NEW       => "entity.job.status.new",
        self::STATUS_ORDERED   => "entity.job.status.ordered",
        self::STATUS_PUBLISHED => "entity.job.status.published",
        self::STATUS_EXPIRED   => "entity.job.status.expired",
        self::STATUS_ARCHIVED  => "entity.job.status.archived",
        self::STATUS_DELETED   => "entity.job.status.deleted",
        self::STATUS_RESTORED  => "entity.job.status.restored",
    );

    public static $CONTRACT_TYPES = array(
        self::CONTRACT_TYPE_FULL_TIME  => "entity.job.contract.cdi",
        self::CONTRACT_TYPE_PART_TIME  => "entity.job.contract.part_time",
        self::CONTRACT_TYPE_INTERNSHIP => "entity.job.contract.internship",
        self::CONTRACT_TYPE_FREELANCE  => "entity.job.contract.freelance",
        self::CONTRACT_TYPE_ALTERNANCE => "entity.job.contract.alternance"
    );

    /** @return array */
    public static function getStatusesKeys()
    {
        return array_keys(self::$STATUSES);
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
            'country'  => strtoupper($this->getCompany()->getCountry()),
            'contract' => strtoupper($this->contract),
            'slug'     => $this->slug ? $this->slug : Urlizer::urlize($this->title),
        );
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->status === Job::STATUS_PUBLISHED;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->status === Job::STATUS_DELETED;
    }

    /**
     * @return int
     */
    public function getTotalViewsCount()
    {
        return $this->listViewsCount + $this->displayViewsCount + $this->apiViewsCount;
    }

    public function incrementApiViews()
    {
        ++$this->apiViewsCount;
    }

    public function incrementDisplayViews()
    {
        ++$this->displayViewsCount;
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
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("company")
     * @JMS\Groups({"api"})
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->company->getName();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("country_name")
     * @JMS\Groups({"api"})
     *
     * @return string
     */
    public function getCountryName()
    {
        return Intl::getRegionBundle()->getCountryName($this->company->getCountry());
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("country_code")
     * @JMS\Groups({"api"})
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->company->getCountry();
    }

    /**
     * @param Company $company
     * @return $this
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
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
     * Get contract
     *
     * @return string
     */
    public function getContractName()
    {
        return isset(self::$CONTRACT_TYPES[$this->contract]) ? self::$CONTRACT_TYPES[$this->contract] : '';
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
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /** @return string */
    public function getStatusName($status)
    {
        return isset(self::$STATUSES[$status]) ? self::$STATUSES[$status] : '';
    }

    /**
     * @param mixed $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getListViewsCount()
    {
        return $this->listViewsCount;
    }

    /**
     * @return int
     */
    public function getDisplayViewsCount()
    {
        return $this->displayViewsCount;
    }

    /**
     * @return int
     */
    public function getApiViewsCount()
    {
        return $this->apiViewsCount;
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

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime $deletedAt
     * @return $this
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * This method returns feed item title
     *
     * @return string
     */
    public function getFeedItemTitle()
    {
        return $this->title;
    }

    /**
     * This method returns feed item description (or content)
     *
     * @return string
     */
    public function getFeedItemDescription()
    {
        return $this->description;
    }

    /**
     * This method returns the name of the route
     *
     * @return string
     */
    public function getFeedItemRouteName()
    {
        return 'job_show';
    }

    /**
     * This method returns the parameters for the route.
     *
     * @return array
     */
    public function getFeedItemRouteParameters()
    {
        return $this->getUrlParameters();
    }

    /**
     * This method returns the anchor to be appended on this item's url
     *
     * @return string The anchor, without the "#"
     */
    public function getFeedItemUrlAnchor()
    {
        return '';
    }

    /**
     * This method returns item publication date
     *
     * @return \DateTime
     */
    public function getFeedItemPubDate()
    {
        return $this->publishStart ?: $this->createdAt;
    }


}
