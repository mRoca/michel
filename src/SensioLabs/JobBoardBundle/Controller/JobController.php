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
     * @Route("/{country}/{contract}/{slug}", name="job_show", requirements={"slug": "[\w\-]+"})
     * @Method("GET")
     * @Template()
     * @ParamConverter("job", class="SensioLabsJobBoardBundle:Job", options={"mapping": {"slug": "slug"}})
     */
    public function showAction(Job $job)
    {
        $job->incrementDisplayViews();

        $em = $this->getDoctrine()->getManager();
        $em->flush($job);

        return array(
            'job' => $job,
        );
    }

    /**
     * @Route("/post", name="job_post")
     * @Template()
     */
    public function postAction(Request $request)
    {
        $postedJobHandler = $this->get('sensiolabs_jobboard.postedjob_handler');

        $fromPreview = false;
        if ($request->isMethod('GET') && $postedJobHandler->hasPostedJob()) {
            $job = $postedJobHandler->getPostedJob();
            $fromPreview = true;
        } else {
            $job = new Job();
        }

        $form = $this->createForm('job', $job);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $postedJobHandler->setPostedJob($job);

            return $this->redirectToRoute('job_preview', $job->getUrlParameters());
        }

        return array(
            'job'          => $job,
            'from_preview' => $fromPreview,
            'form'         => $form->createView(),
        );
    }


    /**
     * @Route("/{country}/{contract}/{slug}/pay", name="job_publish")
     * @Template()
     */
    public function publishAction(Request $request)
    {
        $postedJobHandler = $this->get('sensiolabs_jobboard.postedjob_handler');

        $job = $postedJobHandler->getPostedJob();

        if (!$job instanceof Job) {
            throw $this->createNotFoundException('Posted Job not found');
        }

        $form = $this->createFormBuilder($job)->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $job->setUser($this->getUser());
            $job->setStatus(Job::STATUS_NEW);

            $em = $this->getDoctrine()->getManager();
            $em->persist($job);
            $em->flush();

            $postedJobHandler->clearPostedJob();
            $this->get('session')->getFlashBag()->add('success', 'Your announcement has been added with success');

            return $this->redirectToRoute('homepage');
        }

        return array(
            'job'          => $job,
            'publish_form' => $form->createView(),
        );
    }

    /**
     * @Route("/{country}/{contract}/{slug}/preview", name="job_preview")
     * @Method("GET")
     * @Template()
     */
    public function previewAction()
    {
        $postedJobHandler = $this->get('sensiolabs_jobboard.postedjob_handler');

        $job = $postedJobHandler->getPostedJob();

        if (!$job instanceof Job) {
            throw $this->createNotFoundException('Posted Job not found');
        }

        return array(
            'job' => $job,
        );
    }

    /**
     * @Route("/{country}/{contract}/{slug}/update", name="job_update")
     * @Template()
     * @ParamConverter("job", class="SensioLabsJobBoardBundle:Job", options={"mapping": {"slug": "slug"}})
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

            return $this->redirectToRoute('job_udpate_preview', $job->getUrlParameters());
        }

        return array(
            'job'  => $job,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/{country}/{contract}/{slug}/update/preview", name="job_udpate_preview")
     * @Method("GET")
     * @Template("@SensioLabsJobBoard/Job/update_preview.html.twig")
     * @ParamConverter("job", class="SensioLabsJobBoardBundle:Job", options={"mapping": {"slug": "slug"}})
     */
    public function updatePreviewAction(Job $job = null)
    {
        return array(
            'job' => $job,
        );
    }
}
