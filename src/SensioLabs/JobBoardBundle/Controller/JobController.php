<?php

namespace SensioLabs\JobBoardBundle\Controller;

use SensioLabs\JobBoardBundle\Entity\Job;
use SensioLabs\JobBoardBundle\Form\JobType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class JobController extends Controller
{
    /**
     * @Route("/show", name="job_show")
     * @Template()
     * TODO
     */
    public function showAction()
    {
        return array();
    }

    /**
     * @Route("/post", name="job_post")
     * @Template()
     */
    public function postAction(Request $request)
    {
        $job = new Job();
        $fromPreview = false;

        if ($request->isMethod('GET') && $this->get('session')->has('current_new_job')) {
            if ($this->get('session')->get('current_new_job') instanceof Job) {
                $job = $this->get('session')->get('current_new_job');
                $fromPreview = true;
            } else {
                $this->get('session')->remove('current_new_job');
            }
        }

        $form = $this->createForm('job', $job);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('session')->set('current_new_job', $job);

            return $this->redirect($this->generateUrl('job_preview', $job->getUrlParameters()));
        }

        return array(
            'job'          => $job,
            'from_preview' => $fromPreview,
            'form'         => $form->createView(),
        );
    }

    /**
     * @Route("/{country}/{contract}/{slug}/preview", name="job_preview")
     * @Method("GET")
     * @Template()
     */
    public function previewAction()
    {
        $entity = $this->get('session')->get('current_new_job');

        if (!$entity instanceof Job) {
            return $this->redirect($this->generateUrl('job_post'));
        }

        return array(
            'job' => $entity
        );
    }

    /**
     * @Route("/{country}/{contract}/{slug}/publish", name="job_publish")
     * @Template()
     */
    public function publishAction(Request $request)
    {
        $entity = $this->get('session')->get('current_new_job');

        if (!$entity instanceof Job) {
            return $this->redirect($this->generateUrl('job_post'));
        }

        $form = $this->createFormBuilder($entity)->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->remove('current_new_job');

            return $this->redirect($this->generateUrl('homepage'));
        }

        return array(
            'job'          => $entity,
            'publish_form' => $form->createView(),
        );
    }

    /**
     * @Route("/update", name="job_update")
     * @Template()
     * TODO
     */
    public function updateAction(Request $request)
    {
        return array();
    }
}
