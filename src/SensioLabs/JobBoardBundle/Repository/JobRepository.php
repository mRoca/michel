<?php


namespace SensioLabs\JobBoardBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Entity\User;

class JobRepository extends EntityRepository
{
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
     * @return array
     */
    public function getCountries()
    {
        $qb = $this->filterPublished($this->createQueryBuilder('job'));
        $qb
            ->addSelect("count(job) as total")
            ->addSelect("company.country as code")
            ->join('job.company', 'company')
            ->groupBy("company.country")
            ->orderBy('total', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $country
     * @return array
     */
    public function getContracts($country = null)
    {
        $qb = $this->filterPublished($this->createQueryBuilder('job'));
        $qb
            ->addSelect("count(job) as total")
            ->addSelect("job.contract as code")
            ->groupBy("job.contract")
            ->orderBy('total', 'DESC');

        if ($country) {
            $qb
                ->join('job.company', 'company')
                ->andWhere($qb->expr()->eq("company.country", ':country'))
                ->setParameter(":country", $country);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $status
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQb($status = Job::STATUS_PUBLISHED)
    {
        $qb = $this->filterStatus($this->createQueryBuilder('job'), $status)
            ->addSelect('company')
            ->leftJoin('job.company', 'company')
            ->orderBy('job.id', 'desc');

        return $qb;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFeedQb()
    {
        $now = new \DateTime('today');
        $qb = $this->getListQb();
        $qb
            ->setParameter(':now', $now)
            ->andWhere($qb->expr()->lte('job.publishStart', ':now'))
            ->andWhere($qb->expr()->gt('job.publishEnd', ':now'))
            ->orderBy('job.publishStart', 'desc');

        return $qb;
    }

    /**
     * @param User $user
     * @return array
     */
    public function getQbByUser(User $user)
    {
        $qb = $this->createQueryBuilder('job');
        $qb
            ->addSelect('company')
            ->leftJoin('job.company', 'company')
            ->where($qb->expr()->neq('job.status', ':status'))
            ->setParameter('status', Job::STATUS_DELETED)
            ->andWhere($qb->expr()->eq('job.user', ':user'))
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
