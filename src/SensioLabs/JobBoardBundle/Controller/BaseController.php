<?php

namespace SensioLabs\JobBoardBundle\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Filter\JobFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $data = array();

        $repository = $this->getDoctrine()->getRepository('SensioLabsJobBoardBundle:Job');
        $formFilter = $this->get('form.factory')->createNamed(null, new JobFilterType());

        $formFilter->submit($this->get('request'));

        $filterBuilder = $repository->getListQb();

        $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($formFilter, $filterBuilder);

        $data['jobs'] = $this->get('knp_paginator')->paginate($filterBuilder, $request->query->get('page', 1), Job::LIST_MAX_JOB_ITEMS);
        $repository->incrementListViews($data['jobs']);

        // Add invalid fields not in getData() array
        $data['filters'] = array_fill_keys(array_keys($formFilter->all()), null);
        $data['filters'] = array_merge($data['filters'], $formFilter->getData());

        if ($request->isXmlHttpRequest()) {
            return $this->render('SensioLabsJobBoardBundle:Includes:job_container.html.twig', $data);
        }

        $data['countries'] = $repository->getCountries($data['filters']);
        $data['contracts'] = $repository->getContracts($data['filters']);
        $data['form_filter'] = $formFilter->createView();

        return $data;
    }

    /**
     * @Route("/manage", name="manage")
     * @Template()
     */
    public function manageAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('SensioLabsJobBoardBundle:Job');

        $qb = $repository->getQbByUser($this->getUser());

        /** @var Job[] $jobs */
        $jobs = $this->get('knp_paginator')->paginate($qb, $request->query->get('page', 1), Job::LIST_ADMIN_MAX_JOB_ITEMS);

        return array(
            'jobs' => $jobs
        );
    }
}
