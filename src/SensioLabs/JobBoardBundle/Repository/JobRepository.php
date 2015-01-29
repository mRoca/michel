<?php


namespace SensioLabs\JobBoardBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SensioLabs\JobBoardBundle\Entity\User;


class JobRepository extends EntityRepository
{

    /**
     * @param string $filterColumn
     * @param array $requestFilters
     * @return array
     */
    protected function getFiltersLists($filterColumn, $requestFilters = array())
    {
        $qb = $this->createQueryBuilder('job');
        $qb
            ->addSelect("count(job) as total")
            ->addSelect("job.$filterColumn as code")
            ->groupBy("job.$filterColumn")
            ->orderBy('total', 'DESC');

        foreach ($requestFilters as $column => $value) {
            if ($filterColumn === $column || null === $value) {
                continue;
            }

            $qb->where($qb->expr()->eq("job.$column", ":$column"))
                ->setParameter(":$column", $value);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $filters
     * @return array
     */
    public function getCountries($filters = array())
    {
        return $this->getFiltersLists('country', $filters);
    }

    /**
     * @param array $filters
     * @return array
     */
    public function getContracts($filters = array())
    {
        return $this->getFiltersLists('contract', $filters);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQb()
    {
        $qb = $this->createQueryBuilder('e');
        $qb->orderBy('e.id', 'desc');

        return $qb;
    }

    /**
     * @param User $user
     * @return array
     */
    public function getQbByUser(User $user)
    {
        $qb = $this->createQueryBuilder('job');
        $qb->where($qb->expr()->eq('job.user', ':user'))
            ->setParameter(':user', $user)
            ->orderBy('job.id', 'desc');

        return $qb;
    }

}
