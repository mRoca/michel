<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SensioLabs\JobBoardBundle\SearchRepository\CompanyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class AutocompleteController extends Controller
{

    /**
     * @Route("/autocomplete/city", name="autocomplete_city")
     */
    public function cityAction(Request $request)
    {
        $search = $request->query->get('search');
        $country = $request->query->get('country');

        if (!$search || !$country) {
            throw new InvalidParameterException('Search & country parameters are required');
        }

        /** @var CompanyRepository $repository */
        $repository = $this->get('fos_elastica.manager')->getRepository('SensioLabsJobBoardBundle:Company');
        $type = $this->get('fos_elastica.index.jobboard.company');
        $companies = $type->search($repository->autocompleteCity($search, $country))->getAggregations();

        return new JsonResponse(array_map(function ($aggr) {
            return $aggr['key'];
        }, $companies['citys']['items']['buckets']));
    }

    /**
     * @Route("/autocomplete/company", name="autocomplete_company")
     */
    public function companyAction(Request $request)
    {
        $search = $request->query->get('search');
        $country = $request->query->get('country');
        $city = $request->query->get('city');

        if (!$search || !$country) {
            throw new InvalidParameterException('Search & country parameters are required');
        }

        /** @var CompanyRepository $repository */
        $repository = $this->get('fos_elastica.manager')->getRepository('SensioLabsJobBoardBundle:Company');
        $type = $this->get('fos_elastica.index.jobboard.company');

        $companies = $type->search($repository->autocompleteCompany($search, $country, $city))->getAggregations();

        return new JsonResponse(array_map(function ($aggr) {
            return $aggr['key'];
        }, $companies['names']['items']['buckets']));
    }
}
