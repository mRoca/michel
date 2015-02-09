<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AutocompleteController extends Controller
{

    /**
     * @Route("/autocomplete/city", name="autocomplete_city")
     */
    public function cityAction(Request $request)
    {
        $search = $request->query->get('search');
        $country = $request->query->get('country');

        $repository = $this->getDoctrine()->getRepository('SensioLabsJobBoardBundle:Company');
        $companies = $repository->autocompleteCity($search, $country);

        return new JsonResponse(array_map(function ($company) {
            return $company['city'];
        }, $companies));
    }

    /**
     * @Route("/autocomplete/company", name="autocomplete_company")
     */
    public function companyAction(Request $request)
    {
        $search = $request->query->get('search');
        $country = $request->query->get('country');
        $city = $request->query->get('city');

        $repository = $this->getDoctrine()->getRepository('SensioLabsJobBoardBundle:Company');
        $companies = $repository->autocompleteCompany($search, $country, $city);

        return new JsonResponse(array_map(function ($company) {
            return $company['name'];
        }, $companies));
    }
}
