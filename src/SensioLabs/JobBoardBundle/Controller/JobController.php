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
        $entity = new Job();
        $from_preview = false;

        if ($request->isMethod('GET') && $this->get('session')->has('current_new_job')) {

            if ($this->get('session')->get('current_new_job') instanceof Job) {
                $entity = $this->get('session')->get('current_new_job');
                $from_preview = true;
            } else {
                $this->get('session')->remove('current_new_job');
            }
        }

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->get('session')->set('current_new_job', $entity);

            return $this->redirect($this->generateUrl('job_preview', $entity->getUrlParameters()));
        }

        return array(
            'job'          => $entity,
            'from_preview' => $from_preview,
            'form'         => $form->createView(),
            'errors'       => $this->getAllFormErrorMessages($form)
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

    // ======================================

    /**
     * @param Job $entity
     * @return \Symfony\Component\Form\Form
     */
    private function createCreateForm(Job $entity)
    {
        $form = $this->createForm(new JobType(), $entity, array(
            'action' => $this->generateUrl('job_post'),
            'method' => 'POST'
        ));

        return $form;
    }

    /**
     * @param Form $form
     * @return array
     */
    private function getAllFormErrorMessages(Form $form)
    {
        $errors = array();

        if (count($form->getErrors())) {
            foreach ($form->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $name => $child) {
            $errors = array_merge($errors, $this->getAllFormErrorMessages($child));
        }

        return $errors;
    }

}
