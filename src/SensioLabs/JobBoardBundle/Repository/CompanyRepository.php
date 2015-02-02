<?php


namespace SensioLabs\JobBoardBundle\Repository;


use Doctrine\ORM\EntityRepository;
use SensioLabs\JobBoardBundle\Entity\Company;

class CompanyRepository extends EntityRepository
{

    /**
     * @param string $name
     * @param string $country
     * @param string $city
     * @return Company|null
     */
    public function getUnique($name, $country, $city)
    {
        $qb = $this->createQueryBuilder('company');
        $qb->where($qb->expr()->eq("company.name", ':name'))
            ->andWhere($qb->expr()->eq("company.country", ':country'))
            ->andWhere($qb->expr()->eq("company.city", ':city'))
            ->setParameter(":name", $name)
            ->setParameter(":country", $country)
            ->setParameter(":city", $city);

        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }
}
