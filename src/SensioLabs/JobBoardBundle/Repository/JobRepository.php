<?php


namespace SensioLabs\JobBoardBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SensioLabs\JobBoardBundle\Entity\Job;
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
     * @param bool $onlyPublished
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQb($onlyPublished = true)
    {
        $qb = $this->createQueryBuilder('job');
        $qb->orderBy('job.id', 'desc');

        if ($onlyPublished) {
            $qb->where($qb->expr()->eq('job.status', ':published'))
                ->setParameter('published', Job::STATUS_PUBLISHED);
        }

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

    /**
     * Increase the views count of Jobs entities
     *
     * @param Job[]|\Traversable $jobs
     * @return bool
     */
    public function incrementListViews(\Traversable $jobs)
    {
        $ids = array();
        foreach ($jobs as $job) {
            $ids[] = $job->getId();
        }

        if (!count($ids)) {
            return false;
        }

        $qb = $this->createQueryBuilder('job');
        $qb
            ->update()
            ->set("job.listViewsCount", "job.listViewsCount + 1")
            ->where($qb->expr()->in('job.id', $ids));

        return $qb->getQuery()->execute();
    }

    /**
     * @return Job|null
     */
    public function getRandom()
    {
        $count = $this->createQueryBuilder('job')
            ->select('count(job.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->createQueryBuilder('job')
            ->setMaxResults(1)
            ->setFirstResult(mt_rand(0, $count))
            ->getQuery()
            ->getOneOrNullResult();
    }

}
