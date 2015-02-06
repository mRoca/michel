<?php


namespace SensioLabs\JobBoardBundle\Repository;

use Doctrine\ORM\QueryBuilder;
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
        $qb = $this->filterPublished($this->createQueryBuilder('job'))
            ->addSelect("count(job) as total")
            ->addSelect("job.$filterColumn as code")
            ->groupBy("job.$filterColumn")
            ->orderBy('total', 'DESC');


        foreach ($requestFilters as $column => $value) {
            if ($filterColumn === $column || null === $value) {
                continue;
            }

            $qb->andWhere($qb->expr()->eq("job.$column", ":$column"))
                ->setParameter(":$column", $value);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param null $status
     * @return QueryBuilder
     */
    protected function filterStatus(QueryBuilder $qb, $status = null)
    {
        if (null === $status) {
            return $qb;
        }

        return $qb->where($qb->expr()->eq('job.status', ':status'))
            ->setParameter('status', $status);
    }

    /**
     * @param QueryBuilder $qb
     * @return QueryBuilder
     */
    protected function filterPublished(QueryBuilder $qb)
    {
        return $this->filterStatus($qb, Job::STATUS_PUBLISHED);
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
     * @param string $status
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQb($status = Job::STATUS_PUBLISHED)
    {
        return $this->filterStatus($this->createQueryBuilder('job'), $status)
            ->orderBy('job.id', 'desc');
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
     * @param string $status
     * @return Job|null
     */
    public function getRandom($status = null)
    {
        $count = $this->filterPublished($this->createQueryBuilder('job'), $status)
            ->select('count(job.id)')
            ->getQuery()
            ->getSingleScalarResult();


        return $this->filterPublished($this->createQueryBuilder('job'), $status)
            ->setMaxResults(1)
            ->setFirstResult(mt_rand(0, $count))
            ->getQuery()
            ->getOneOrNullResult();
    }

}
