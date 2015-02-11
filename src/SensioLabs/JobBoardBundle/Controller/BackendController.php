<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\SearchRepository\JobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BackendController extends Controller
{
    /**
     * @Route("/backend", name="backend_list")
     * @Template()
     */
    public function listAction(Request $request)
    {
        $status = strtoupper($request->query->get('status', Job::STATUS_PUBLISHED));

        if (!in_array($status, Job::getStatusesKeys())) {
            $status = null;
        }

        /** @var JobRepository $searchRepository */
        $searchRepository = $this->get('fos_elastica.manager')->getRepository('SensioLabsJobBoardBundle:Job');
        $paginator = $searchRepository->createPaginatorAdapter($searchRepository->getListQuery($status));

        /** @var Job[] $jobs */
        $jobs = $this->get('knp_paginator')->paginate(
            $paginator,
            $request->query->get('page', 1),
            Job::LIST_ADMIN_MAX_JOB_ITEMS,
            array(
                'defaultSortFieldName' => 'created_at.raw',
                'defaultSortDirection' => 'asc',
            )
        );

        $actionForms = array(
            'job_delete' => array(),
            'job_restore' => array(),
        );

        foreach ($jobs as $job) {
            $formType = $job->isDeleted() ? 'job_restore' : 'job_delete';
            $formAction = $job->isDeleted() ? 'backend_restore' : 'backend_delete';
            $actionForms[$formType][$job->getId()] = $this->get('form.factory')->createNamed(
                $formType . $job->getId(),
                $formType,
                $job,
                array('action' => $this->generateUrl($formAction, array('id' => $job->getId())),)
            )->createView();
        }

        return array(
            'status'        => $status,
            'jobs'          => $jobs,
            'delete_forms'  => $actionForms['job_delete'],
            'restore_forms' => $actionForms['job_restore'],
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

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            if ($job->getStatus() !== $oldjob->getStatus()) {
                $this->get('sensiolabs_jobboard.mailer.job')->jobPublish($job, $oldjob);
            }

            return $this->redirectToRoute('backend_list', array('status' => 'all'));
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

        $deleteForm = $this->get('form.factory')->createNamed('job_delete' . $job->getId(), 'job_delete', $job);
        $deleteForm->handleRequest($request);
        if ($deleteForm->isValid()) {
            $job->setStatus(Job::STATUS_DELETED);
            $em->flush();
        } else {
            $errors = $deleteForm->getErrors();
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        parse_str(parse_url($request->headers->get('referer'), PHP_URL_QUERY), $params);
        return $this->redirectToRoute('backend_list', $params);
    }

    /**
     * @Route("/backend/{id}/restore", name="backend_restore")
     * @ParamConverter("job", class="SensioLabsJobBoardBundle:Job")
     */
    public function restoreAction(Request $request, Job $job)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $restoreForm = $this->get('form.factory')->createNamed('job_restore' . $job->getId(), 'job_restore', $job);
        $restoreForm->handleRequest($request);
        if ($restoreForm->isValid()) {
            $job->setStatus(Job::STATUS_RESTORED);
            $em->flush();

            $this->addFlash('success',
                $this->get('translator')->trans(
                    'messages.success.job_restored',
                    array('%job.title%' => $job->getTitle())
                )
            );
        } else {
            $errors = $restoreForm->getErrors();
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        parse_str(parse_url($request->headers->get('referer'), PHP_URL_QUERY), $params);
        return $this->redirectToRoute('backend_list', $params);
    }
}
