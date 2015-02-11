<?php

namespace SensioLabs\JobBoardBundle\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Filter\Form\Type\JobFilterType;
use SensioLabs\JobBoardBundle\SearchRepository\JobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        /** @var JobRepository $searchRepository */
        $searchRepository = $this->get('fos_elastica.manager')->getRepository('SensioLabsJobBoardBundle:Job');
        $jobIndex = $this->get('fos_elastica.index.jobboard.job');

        $formFilter = $this->get('form.factory')->createNamed(null, new JobFilterType());
        $formFilter->submit($request);

        // Add invalid fields not in getData() array
        $data['filters'] = array_fill_keys(array_keys($formFilter->all()), null);
        $data['filters'] = array_merge($data['filters'], $formFilter->getData());

        $paginator = $searchRepository->createPaginatorAdapter($searchRepository->getPublishedListQuery($data['filters']));

        $data['jobs'] = $this->get('knp_paginator')->paginate($paginator, $request->query->get('page', 1), Job::LIST_MAX_JOB_ITEMS);
        $repository->incrementListViews($data['jobs']);

        if ($request->isXmlHttpRequest()) {
            return $this->render('SensioLabsJobBoardBundle:Includes:job_container.html.twig', $data);
        }

        $countries = $jobIndex->search($searchRepository->getCountriesQuery())->getAggregations();
        $contracts = $jobIndex->search($searchRepository->getContractsQuery($data['filters']['country']))->getAggregations();

        $data['countries'] = $countries['countries']['items']['buckets'];
        $data['contracts'] = $contracts['contracts']['items']['buckets'];
        $data['form_filter'] = $formFilter->createView();

        return $data;
    }

    /**
     * @Route("/rss", name="feed_rss")
     */
    public function feedAction()
    {
        $formFilter = $this->get('form.factory')->createNamed(null, new JobFilterType());
        $formFilter->submit($this->get('request'));

        /** @var JobRepository $searchRepository */
        $searchRepository = $this->get('fos_elastica.manager')->getRepository('SensioLabsJobBoardBundle:Job');
        $paginator = $searchRepository->find($searchRepository->getPublishedListQuery($formFilter->getData()));

        $feed = $this->get('eko_feed.feed.manager')->get('article');
        $feed->addFromArray($paginator);

        return new Response($feed->render('rss'));
    }

    /**
     * @Route("/manage", name="manage")
     * @Template()
     */
    public function manageAction(Request $request)
    {

        /** @var JobRepository $searchRepository */
        $searchRepository = $this->get('fos_elastica.manager')->getRepository('SensioLabsJobBoardBundle:Job');
        $paginator = $searchRepository->createPaginatorAdapter($searchRepository->getListByUserQuery($this->getUser()));

        /** @var Job[] $jobs */
        $jobs = $this->get('knp_paginator')->paginate($paginator, $request->query->get('page', 1), Job::LIST_ADMIN_MAX_JOB_ITEMS);

        return array(
            'jobs' => $jobs
        );
    }
}
