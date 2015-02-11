<?php

namespace SensioLabs\JobBoardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\ElasticaBundle\Configuration\Search;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Company
 *
 * @ORM\Entity(repositoryClass="SensioLabs\JobBoardBundle\Repository\CompanyRepository")
 * @Search(repositoryClass="SensioLabs\JobBoardBundle\SearchRepository\CompanyRepository")
 *
 * @ORM\Table(name="companies", uniqueConstraints={@ORM\UniqueConstraint(name="search_idx", columns={"name", "country", "city"})})
 *
 */
class Company
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="company.name.not_blank")
     * @JMS\Groups({"post_session"})
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=2)
     * @Assert\NotBlank(message="company.country.not_blank")
     * @JMS\Groups({"post_session"})
     */
    protected $country;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="company.city.not_blank")
     * @JMS\Groups({"post_session"})
     */
    protected $city;

    /**
     * @var ArrayCollection|Job[]
     * @ORM\OneToMany(targetEntity="Job", mappedBy="company", fetch="EXTRA_LAZY")
     */
    protected $jobs;

    public function __construct()
    {
        $this->jobs = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
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
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

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
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return ArrayCollection|Job[]
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * @param ArrayCollection|Job[] $jobs
     * @return $this
     */
    public function setJobs($jobs)
    {
        $this->jobs = $jobs;

        return $this;
    }

}
