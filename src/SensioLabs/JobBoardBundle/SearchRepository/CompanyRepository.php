<?php

namespace SensioLabs\JobBoardBundle\SearchRepository;

use FOS\ElasticaBundle\Repository;

class CompanyRepository extends Repository
{
    protected function autocompleteQuery($searchColumn, $search, $country, $city = null)
    {
        if ($searchColumn && $search) {
            $query = new \Elastica\Query\Bool();
            $fieldQuery = new \Elastica\Query\QueryString();
            $fieldQuery->setFields(array($searchColumn));
            $fieldQuery->setQuery($search);
            $query->addMust($fieldQuery);
        } else {
            $query = new \Elastica\Query\MatchAll();
        }

        $query = new \Elastica\Query($query);
        $query->setSize(0);

        $filters = new \Elastica\Filter\Bool();
        $filters->addMust(new \Elastica\Filter\Term(array('country' => $country)));

        if ($city) {
            $filters->addMust(new \Elastica\Filter\Term(array('city.raw' => $city)));
        }

        $termsAgg = new \Elastica\Aggregation\Terms('items');
        $termsAgg->setField("$searchColumn.raw");
        $termsAgg->setSize(10);

        $aggrFilter = new \Elastica\Aggregation\Filter($searchColumn . 's');
        $aggrFilter->setFilter($filters);
        $aggrFilter->addAggregation($termsAgg);

        $query->addAggregation($aggrFilter);

        return $query;
    }

    public function autocompleteCity($search, $country)
    {
        return $this->autocompleteQuery('city', $search, $country);
    }

    public function autocompleteCompany($search, $country, $city)
    {
        return $this->autocompleteQuery('name', $search, $country, $city);
    }
}
