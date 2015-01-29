<?php

namespace SensioLabs\JobBoardBundle\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Filter\JobFilterType;
use SensioLabs\JobBoardBundle\Repository\JobRepository;
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
        $em = $this->getDoctrine()->getManager();

        /** @var JobRepository $repository */
        $repository = $em->getRepository('SensioLabsJobBoardBundle:Job');
        $formFilter = $this->get('form.factory')->createNamed(null, new JobFilterType());

        $formFilter->submit($this->get('request'));

        $filterBuilder = $repository->getListQb();

        $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($formFilter, $filterBuilder);

        $data['jobs'] = $this->get('knp_paginator')->paginate($filterBuilder, $request->query->get('page', 1), Job::LIST_MAX_JOB_ITEMS);

        $data['filters'] = $formFilter->getData();
        //Add invalid fields not in getData() array
        foreach ($formFilter->all() as $field) {
            if (!isset($data['filters'][$field->getName()])) {
                $data['filters'][$field->getName()] = null;
            }
        }

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
        $em = $this->getDoctrine()->getManager();
        /** @var JobRepository $repository */
        $repository = $em->getRepository('SensioLabsJobBoardBundle:Job');

        $qb = $repository->getQbByUser($this->getUser());

        /** @var Job[] $jobs */
        $jobs = $this->get('knp_paginator')->paginate($qb, $request->query->get('page', 1), Job::LIST_ADMIN_MAX_JOB_ITEMS);

        return array(
            'jobs' => $jobs
        );
    }
}
