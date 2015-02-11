<?php


namespace SensioLabs\JobBoardBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use SensioLabs\JobBoardBundle\Entity\Job;

class JobRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $qb
     * @return QueryBuilder
     */
    protected function filterPublished(QueryBuilder $qb)
    {
        return $qb->where($qb->expr()->eq('job.status', ':status'))
            ->setParameter('status', Job::STATUS_PUBLISHED);
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
        $count = $this->filterPublished($this->createQueryBuilder('job'))
            ->select('count(job.id)')
            ->getQuery()
            ->getSingleScalarResult();


        return $this->filterPublished($this->createQueryBuilder('job'))
            ->setMaxResults(1)
            ->setFirstResult(mt_rand(0, $count))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $days
     * @return mixed
     */
    public function deleteOldJobs($days)
    {
        $qb = $this->createQueryBuilder('job');
        $qb->delete()
            ->where($qb->expr()->eq('job.status', ':status'))
            ->andWhere($qb->expr()->lte('job.deletedAt', ':date'))
            ->setParameter('status', Job::STATUS_DELETED)
            ->setParameter('date', new \DateTime("tomorrow - $days days"));

        return $qb->getQuery()->execute();
    }

}
