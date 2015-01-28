<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class BackendController extends Controller
{
    /**
     * @Route("/backend", name="backend_list")
     * @Template()
     */
    public function listAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('SensioLabsJobBoardBundle:Job');

        $qb = $repository->getListQb()->getQuery();

        /** @var Job[] $jobs */
        $jobs = $this->get('knp_paginator')->paginate(
            $qb,
            $request->query->get('page', 1),
            Job::LIST_ADMIN_MAX_JOB_ITEMS,
            array(
                'defaultSortFieldName' => 'job.createdAt',
                'defaultSortDirection' => 'asc',
            )
        );

        $deleteForms = array();
        foreach ($jobs as $job) {
            $deleteForms[$job->getId()] = $this->createForm('job_delete', $job, array(
                'action' => $this->generateUrl('backend_delete', array('id' => $job->getId())),
            ))->createView();
        }

        return array(
            'jobs'         => $jobs,
            'delete_forms' => $deleteForms
        );
    }

    /**
     * @Route("/backend/{id}/edit", name="backend_edit")
     * @Template()
     * @ParamConverter("job", class="SensioLabsJobBoardBundle:Job")
     */
    public function editAction(Request $request, Job $job)
    {
        $oldjob = clone($job);

        $form = $this->createForm('adminjob', $job);
        $form->handleRequest($request);

        if ($form->isValid()) {

            // If the "Publish" checkbox has been changed, we switch the job status
            if ($form->get('publish')->getData() !== $oldjob->isPublished()) {
                $job->setStatus($form->get('publish')->getData() ? Job::STATUS_PUBLISHED : Job::STATUS_NEW);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            if ($job->getStatus() !== $oldjob->getStatus()) {
                $this->get('sensiolabs_jobboard.mailer.job')->jobPublish($job, $oldjob);
            }

            return $this->redirectToRoute('backend_list');
        }

        return array(
            'job'  => $job,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/backend/{id}/delete", name="backend_delete")
     * @ParamConverter("job", class="SensioLabsJobBoardBundle:Job")
     */
    public function deleteAction(Request $request, Job $job)
    {
        $em = $this->getDoctrine()->getManager();

        $deleteForm = $this->createForm('job_delete', $job);
        $deleteForm->handleRequest($request);
        if ($deleteForm->isValid()) {
            $job->setStatus(Job::STATUS_DELETED);
            $em->flush();
        } else {
            $session = $this->get('session');
            $errors = $deleteForm->getErrors();
            foreach ($errors as $error) {
                $session->getFlashBag()->add('error', $error->getMessage());
            }
        }

        return new RedirectResponse($this->generateUrl('backend_list'));
    }
}
