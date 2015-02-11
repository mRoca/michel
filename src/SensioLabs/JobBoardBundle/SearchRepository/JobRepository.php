<?php

namespace SensioLabs\JobBoardBundle\SearchRepository;

use FOS\ElasticaBundle\Repository;
use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Entity\User;

class JobRepository extends Repository
{
    public function getCountriesQuery()
    {
        $query = new \Elastica\Query(new \Elastica\Query\MatchAll());
        $query->setSize(0);

        $filters = new \Elastica\Filter\Bool();
        $filters->addMust(new \Elastica\Filter\Term($this->getPublishedTerm()));

        $termsAgg = new \Elastica\Aggregation\Terms('items');
        $termsAgg->setField("company.country");

        $aggrFilter = new \Elastica\Aggregation\Filter('countries');
        $aggrFilter->setFilter($filters);
        $aggrFilter->addAggregation($termsAgg);

        $query->addAggregation($aggrFilter);

        return $query;
    }

    public function getContractsQuery($country)
    {
        $query = new \Elastica\Query(new \Elastica\Query\MatchAll());
        $query->setSize(0);

        $filters = new \Elastica\Filter\Bool();
        $filters->addMust(new \Elastica\Filter\Term($this->getPublishedTerm()));

        if ($country) {
            $filters->addMust(new \Elastica\Filter\Term(array('company.country' => $country)));
        } else {
            $filters->addMust(new \Elastica\Filter\Exists('company.country'));
        }

        $termsAgg = new \Elastica\Aggregation\Terms('items');
        $termsAgg->setField("contract");

        $aggrFilter = new \Elastica\Aggregation\Filter('contracts');
        $aggrFilter->setFilter($filters);
        $aggrFilter->addAggregation($termsAgg);

        $query->addAggregation($aggrFilter);

        return $query;
    }

    public function getPublishedListQuery($filtersValues = array())
    {
        $searchQuery = new \Elastica\Query\MatchAll();

        $today = new \DateTime('today');

        $filters = new \Elastica\Filter\Bool();
        $filters->addMust(new \Elastica\Filter\Term($this->getPublishedTerm()));
        $filters->addMust(new \Elastica\Filter\Range('publish_start', array('lte' => $today->format('Y-m-d'))));
        $filters->addMust(new \Elastica\Filter\Range('publish_end', array('gt' => $today->format('Y-m-d'))));

        if (isset($filtersValues['country']) && $filtersValues['country']) {
            $filters->addMust(new \Elastica\Filter\Term(array('company.country' => $filtersValues['country'])));
        }

        if (isset($filtersValues['contract']) && $filtersValues['contract']) {
            $filters->addMust(new \Elastica\Filter\Term(array('contract' => $filtersValues['contract'])));
        }

        $query = new \Elastica\Query();
        $query->setQuery(new \Elastica\Query\Filtered($searchQuery, $filters));
        $query->setSort(array('publish_start.raw' => array('order' => 'desc')));
        $query->setSize(Job::LIST_ADMIN_MAX_JOB_ITEMS);

        return $query;
    }

    public function getListQuery($status)
    {
        $searchQuery = new \Elastica\Query\MatchAll();
        $query = new \Elastica\Query();

        if ($status) {
            $filters = new \Elastica\Filter\Bool();
            $filters->addMust(new \Elastica\Filter\Term(array('status' => $status)));
            $query->setQuery(new \Elastica\Query\Filtered($searchQuery, $filters));
        } else {
            $query->setQuery($searchQuery);
        }

        $query->setSort(array('created_at.raw' => array('order' => 'asc')));

        return $query;
    }

    public function getListByUserQuery(User $user)
    {
        $searchQuery = new \Elastica\Query\MatchAll();
        $query = new \Elastica\Query();

        $filters = new \Elastica\Filter\Bool();
        $filters->addMust(new \Elastica\Filter\Term(array('user.uuid' => $user->getUuid())));
        $filters->addMustNot(new \Elastica\Filter\Term(array('status' => Job::STATUS_DELETED)));
        $query->setQuery(new \Elastica\Query\Filtered($searchQuery, $filters));
        $query->setSort(array('created_at.raw' => array('order' => 'desc')));

        return $query;
    }

    protected function getPublishedTerm()
    {
        return array('status' => Job::STATUS_PUBLISHED);
    }
}
