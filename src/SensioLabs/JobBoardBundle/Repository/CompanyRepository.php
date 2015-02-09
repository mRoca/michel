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

    /**
     * @param string $search
     * @param string $country
     * @return mixed
     */
    public function autocompleteCity($search, $country)
    {
        return $this->autocompleteQb('company.city', $search, $country)->getQuery()->execute();
    }

    /**
     * @param string $search
     * @param string $country
     * @param string $city
     * @return array
     */
    public function autocompleteCompany($search, $country, $city)
    {
        return $this->autocompleteQb('company.name', $search, $country, $city)->getQuery()->execute();
    }

    /**
     * @param $column
     * @param string $search
     * @param string $country
     * @param string $city
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function autocompleteQb($column, $search, $country = null, $city = null)
    {
        $qb = $this->createQueryBuilder('company');
        $qb->select($column)
            ->distinct()
            ->where($qb->expr()->like($column, ':search'))
            ->setParameter(":search", "$search%")
            ->setMaxResults(10)
            ->orderBy($column, 'asc');

        if ($country) {
            $qb->andWhere($qb->expr()->eq("company.country", ':country'))
                ->setParameter(":country", $country);
        }

        if ($city) {
            $qb->andWhere($qb->expr()->eq("company.city", ':city'))
                ->setParameter(":city", $city);
        }

        return $qb;
    }

}
