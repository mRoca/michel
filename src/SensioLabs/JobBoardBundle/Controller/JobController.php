<?php

namespace SensioLabs\JobBoardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SensioLabs\JobBoardBundle\Entity\Job;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

            return $this->redirectToRoute('job_preview', $job->getUrlParameters());
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
     * @ParamConverter("job", class="SensioLabsJobBoardBundle:Job")
     */
    public function previewAction(Job $job = null)
    {
        if ($job instanceof Job) {
            return $this->render('@SensioLabsJobBoard/Job/update_preview.html.twig', array('job' => $job));
        }

        $job = $this->get('session')->get('current_new_job');

        if (!$job instanceof Job) {
            return $this->redirectToRoute('job_post');
        }

        return array(
            'job' => $job,
        );
    }

    /**
     * @Route("/{country}/{contract}/{slug}/update", name="job_update")
     * @Template()
     * @ParamConverter("job", class="SensioLabsJobBoardBundle:Job")
     */
    public function updateAction(Request $request, Job $job)
    {
        if (!$this->get('security.authorization_checker')->isGranted('edit', $job)) {
            throw new AccessDeniedException('Unauthorised access');
        }

        $oldjob = clone($job);

        $form = $this->createForm('job', $job);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($job);
            $em->flush();

            if ($job->isPublished()) {
                $this->get('sensiolabs_jobboard.mailer.job')->jobUpdate($job, $oldjob);
            }

            return $this->redirectToRoute('job_preview', $job->getUrlParameters());
        }

        return array(
            'job'  => $job,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{country}/{contract}/{slug}/pay", name="job_publish")
     * @Template()
     */
    public function publishAction(Request $request)
    {
        $job = $this->get('session')->get('current_new_job');

        if (!$job instanceof Job) {
            return $this->redirectToRoute('job_post');
        }

        $form = $this->createFormBuilder($job)->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $job->setUser($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($job);
            $em->flush();

            $session = $this->get('session');
            $session->remove('current_new_job');
            $session->getFlashBag()->add('success', 'Your announcement has been added with success');

            return $this->redirectToRoute('homepage');
        }

        return array(
            'job'          => $job,
            'publish_form' => $form->createView(),
        );
    }

}
